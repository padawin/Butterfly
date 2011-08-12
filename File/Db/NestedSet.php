<?php
/**
 *
 * Comment
 *
 *
 */


abstract class Butterfly_Db_NestedSet extends Butterfly_Db_Abstract
{

    const POSITION_APPEND = 1;
    const POSITION_AFTER = 2;
    const POSITION_BEFORE = 3;
    const POSITION_POP = 4;
    const POSITION_ALPHA = 5;

    public function getLabel()
    {
        return $this->nestedset_label;
    }

    public function getLeft()
    {
        return $this->nestedset_left;
    }

    public function getRight()
    {
        return $this->nestedset_right;
    }

    public static function loadOrdered($class)
    {
        $obj = new $class;
        return $obj->_fetch($class, '', array(), 'ORDER BY nestedset_left');
    }

    public static function loadLast($class)
    {
        $obj = new $class;
        return $obj->_fetchOne(
            $class,
            'nestedset_right = (SELECT MAX(nestedset_right) FROM ' . $obj->_tableName . ')',
            array()
        );
    }

    /**
     *
     * Add an element in the tree
     *
     * @param int $reference    id of an existing element in the tree to insert this
     * @param int $pos          position to insert $this
     *            values :
     *              - self::POSITION_BEFORE     => before ref
     *              - self::POSITION_AFTER      => after ref
     *              - self::POSITION_APPEND     => last element of ref
     *              - self::POSITION_IN_ALPHA   => in ref in alpha order (ref must be allready sorted)
     *              - self::POSITION_POP        => first element of ref
     *
     */
    public function add($reference = null, $position = self::POSITION_APPEND)
    {
        $last = $this->loadLast($this->_getClass());

        //INSERT at the end of the tree
        $this->nestedset_left = $last->nestedset_right + 1;
        $this->nestedset_right = $last->nestedset_right + 2;
        $this->save();

        //move this
        if ($reference != null) {
            $this->move($reference, $position);
        }
    }

    public function delete()
    {
        $last = $this->loadLast($this->_getClass());
        $this->move($last, self::POSITION_AFTER);
        parent::delete();
    }

    /**
     *
     * @param $reference element which will be used as reference
     * for the movement
     * @param $position 'before', 'after' or 'in' the reference element
     *
     *
     */
    public function move($reference, $position = self::POSITION_APPEND)
    {
        $this->getAdapter();

        if ($this->nestedset_left < $reference->nestedset_left && $this->nestedset_right > $reference->nestedset_right) {
            return false;
        }

        $oldLeft = $this->nestedset_left;
        $oldRight = $this->nestedset_right;

        $width = $this->_getNodeWidth();

        $delta = $this->_makeSpace($width, $reference, $position);

        //----------------------------------

        //move this
        $sqlMoveThis = '
            UPDATE ' . $this->_tableName . '
            SET
                nestedset_left = nestedset_left + :delta,
                nestedset_right = nestedset_right + :delta
            WHERE
                nestedset_left >= :left_this AND
                nestedset_right <= :right_this
        ';
        $valuesMoveThis = array(
            'delta' => $delta,
            'left_this' => $this->nestedset_left,
            'right_this' => $this->nestedset_right
        );
        $stmtMoveThis = $this->_db->prepare($sqlMoveThis);
        $stmtMoveThis->execute($valuesMoveThis);

        //----------------------------------------------

        //move the elements to the right of the last position of this to remove the space
        //right
        $sqlRight = '
            UPDATE ' . $this->_tableName . '
            SET nestedset_right = nestedset_right - :width
            WHERE
                nestedset_right > :old_right
        ';
        $valuesRight = array(
            'width' => $width,
            'old_right' => $this->nestedset_right
        );

        $stmtRight = $this->_db->prepare($sqlRight);
        $stmtRight->execute($valuesRight);

        //then left
        $sqlLeft = '
            UPDATE ' . $this->_tableName . '
            SET nestedset_left = nestedset_left - :width
            WHERE
                nestedset_left > :old_right
        ';
        $valuesLeft = array(
            'width' => $width,
            'old_right' => $this->nestedset_right
        );

        $stmtLeft = $this->_db->prepare($sqlLeft);
        $stmtLeft->execute($valuesLeft);

        //update object
        $this->nestedset_left = $this->nestedset_left + $delta - ($oldLeft == $this->nestedset_left ? $width : 0);
        $this->nestedset_right = $this->nestedset_right + $delta - ($oldRight == $this->nestedset_right ? $width : 0);

        //update id_parent
        $parent = $this->getParent();
        if (!empty($parent)) {
            $this->id_parent = $parent->getPkValue();
        }
        else {
            $this->id_parent = null;
        }
        $this->save();

        return true;
    }

    protected function _makeSpace($width, &$reference, $position)
    {
        $sqlLeft = '
            UPDATE ' . $this->_tableName . '
            SET nestedset_left = nestedset_left + :width
        ';
        $valuesLeft = array(
            'width' => $width
        );

        $sqlRight = '
            UPDATE ' . $this->_tableName . '
            SET nestedset_right = nestedset_right + :width
        ';
        $valuesRight = array(
            'width' => $width
        );

        switch ($position) {
            case self::POSITION_ALPHA:
            //first element of $reference
            case self::POSITION_POP:
                break;
            case self::POSITION_BEFORE:
                //move the reference and the next nodes to insert this
                //left
                $sqlLeft .= '
                    WHERE nestedset_left >= :reference_left
                ';
                $valuesLeft['reference_left'] = $reference->nestedset_left;

                //then right
                $sqlRight .= '
                    WHERE nestedset_right > :reference_left
                ';
                $valuesRight['reference_left'] = $reference->nestedset_left;

                if ($this->nestedset_left > $reference->nestedset_left) {
                    $this->nestedset_left = $this->nestedset_left + $width;
                    $this->nestedset_right = $this->nestedset_right + $width;
                }

                $reference->nestedset_left = $reference->nestedset_left + $width;
                $reference->nestedset_right = $reference->nestedset_right + $width;

                $delta = $reference->nestedset_left - $this->nestedset_right - 1;
                break;
            case self::POSITION_AFTER:
                //move the followings of the reference to insert this
                //left
                $sqlLeft .= '
                    WHERE nestedset_left > :reference_right
                ';
                $valuesLeft['reference_right'] = $reference->nestedset_right;

                //then right
                $sqlRight .= '
                    WHERE nestedset_right > :reference_right
                ';
                $valuesRight['reference_right'] = $reference->nestedset_right;

                if ($this->nestedset_left > $reference->nestedset_right) {
                    $this->nestedset_left = $this->nestedset_left + $width;
                    $this->nestedset_right = $this->nestedset_right + $width;
                }

                $delta = $reference->nestedset_right - $this->nestedset_left + 1;
                break;
            case self::POSITION_APPEND:
            default:
                //move the right of the reference and its followings to insert this
                //left
                $sqlLeft .= '
                    WHERE nestedset_left > :reference_right
                ';
                $valuesLeft['reference_right'] = $reference->nestedset_right;

                //then right
                $sqlRight .= '
                    WHERE nestedset_right >= :reference_right
                ';
                $valuesRight['reference_right'] = $reference->nestedset_right;

                if ($this->nestedset_left > $reference->nestedset_right) {
                    $this->nestedset_left = $this->nestedset_left + $width;
                    $this->nestedset_right = $this->nestedset_right + $width;
                }

                $reference->nestedset_right = $reference->nestedset_right + $width;

                $delta = $reference->nestedset_right - $this->nestedset_right - 1;
                break;

        }

        //move left
        $stmt = $this->_db->prepare($sqlLeft);
        $stmt->execute($valuesLeft);

        //then right
        $stmt = $this->_db->prepare($sqlRight);
        $stmt->execute($valuesRight);

        return $delta;
    }

    private function _getNodeWidth()
    {
        return $this->nestedset_right - $this->nestedset_left + 1;
    }

    /**
     *
     * Load all the rows which are children of $this
     * That means each row which have a left lower than $this->left and
     * greater than $this->right
     *
     */
    public function getChildren()
    {
        return $this->_fetch(
            $this->_getClass(),
            'nestedset_left > :left AND nestedset_left < :right',
            array(
                'left' => $this->nestedset_left,
                'right' => $this->nestedset_right
            )
        );
    }

    /**
     *
     * Return the parent Node of this
     *
     */
    public function getParent()
    {
        return $this->_fetchOne(
            $this->_getClass(),
            'nestedset_left < :left AND nestedset_right > :right',
            array(
                'left' => $this->nestedset_left,
                'right' => $this->nestedset_right
            ),
            'ORDER BY nestedset_left DESC'
        );
    }

    public function getLeftSibling()
    {
        return $this->_fetchOne(
            $this->_getClass(),
            'nestedset_right =  :left - 1',
            array(
                'left' => $this->nestedset_left
            )
        );
    }

    public function getRightSibling()
    {
        return $this->_fetchOne(
            $this->_getClass(),
            'nestedset_left =  :right + 1',
            array(
                'right' => $this->nestedset_right
            )
        );
    }

    public function canBeMovedToLeft()
    {
        return !$this->getParent()
                && $this->getLeft() != 1
                ||
                $this->getParent()
                && $this->getParent()->getLeft() < $this->getLeft() - 1;
    }

    public function canBeMovedToRight()
    {
        $last = self::loadLast(get_class($this));
        return !$this->getParent()
                && $this->getPkValue() != $last->getPkValue()
                ||
                $this->getParent()
                && $this->getParent()->getRight() > $this->getRight() + 1;
    }

    public static function getMax()
    {
        $obj = new static;
        $sql = '
            SELECT
                MAX(nestedset_right) AS max
            FROM ' . $obj->_tableName . '
        ';

        $stmt = static::getDbAdapter()->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);

        return $return['max'];
    }
}
