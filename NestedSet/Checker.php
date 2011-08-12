<?php

class Butterfly_NestedSet_Checker
{
    private $_table;

    public function __construct($table)
    {
        $this->_table = $table;
    }

    public function checkBounds()
    {
        $db = Butterfly_Db_NestedSet::getDbAdapter();

        $sql = '
            SELECT
                MIN(nestedset_left) AS left_bound,
                MAX(nestedset_right) AS right_bound,
                COUNT(*) AS total
            FROM
                ' . $this->_table;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch();

        if ($row['left_bound'] != 1) {
            return 'ERREUR : L\'extrémité gauche doit être égale à 1 (ici : ' . $row['left_bound'] . ')';
        }

        if ($row['right_bound'] != $row['total'] * 2) {
            return 'ERREUR : L\'extrémité droite doit être égale à 2 fois le nombre d\'éléments (ici : ' . $row['right_bound'] . ', nombre d\'éléments : ' . $row['total'] . ')';
        }

        return 'OK';
    }

    public function checkNumber()
    {
        $db = Butterfly_Db_NestedSet::getDbAdapter();

        $sql = '
            SELECT
                COUNT(nestedset_left) + COUNT(nestedset_right) AS nb_sides,
                COUNT(*) AS total
            FROM
                ' . $this->_table;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch();

        if ($row['nb_sides'] != $row['total'] * 2) {
            return 'ERREUR : Le nombre de bornes doit être égal a 2 fois le nombre total d\'éléments';
        }

        return 'OK';
    }

    public function checkUnicity()
    {
        $db = Butterfly_Db_NestedSet::getDbAdapter();

        $sqlLeft = '
            SELECT
                nestedset_left,
                COUNT(*) AS number

            FROM
                ' . $this->_table . '
            GROUP BY
                nestedset_left
        ';

        $sqlRight = '
            SELECT
                nestedset_left,
                COUNT(*) AS number

            FROM
                ' . $this->_table . '
            GROUP BY
                nestedset_left
        ';

        $sqlTotal = '
            SELECT
                COUNT(*) AS total
            FROM
                ' . $this->_table
        ;

        $stmtLeft = $db->prepare($sqlLeft);
        $stmtLeft->execute();

        $stmtRight = $db->prepare($sqlRight);
        $stmtRight->execute();

        $stmtTotal = $db->prepare($sqlTotal);
        $stmtTotal->execute();

        $rowsLeft = $stmtLeft->fetchAll();
        $rowsRight = $stmtRight->fetchAll();
        $rowTotal = $stmtTotal->fetch();

        $rows = array_merge($rowsLeft, $rowsRight);
        if (count($rows) != 2 * $rowTotal['total']) {
            return 'ERREUR : Chaque borne ne peut pas être présente plus d\'une fois dans l\'arbre';
        }

        return 'OK';
    }

    /**
     *
     * Not working...
     *
     */
    public function checkCross()
    {
        $sql = '
            SELECT
                t1.nestedset_left,
                t1.nestedset_right,
                t2.nestedset_left,
                t2.nestedset_right
            FROM
                ' . $this->_table . ' t1
                INNER JOIN ' . $this->_table . ' t2 ON t1.nestedset_left < t2.nestedset_right AND t2.nestedset_left < t1.nestedset_right
        ';
        echo $sql;die;
    }

    public function checkOrder()
    {
        $db = Butterfly_Db_NestedSet::getDbAdapter();

        $sql = '
            SELECT
                *
            FROM
                ' . $this->_table . '
            WHERE
                nestedset_left > nestedset_right
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch();

        if ($row) {
            return 'ERROR : le champ nestedset_left doit TOUJOURS être inférieur au champ nestedset_right de l\'enregistrement';
        }
        else {
            return 'OK';
        }
    }
}
