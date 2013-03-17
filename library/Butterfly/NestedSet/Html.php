<?php

class Butterfly_NestedSet_Html
{

	private $_nestedSetCollection = array();

	private $_options;

	private $_parentsList = array();

	/**
	 *
	 * @param array $options array of options
	 * available values : label-link (string), actions (array)
	 * can use token %ID% in the urls to use the current node's id inside
	 *
	 */
	public function __construct(array $nestedCollection, $options = array())
	{
		$this->_nestedSetCollection = $nestedCollection;

		$this->_options = $options;
	}

	/**
	 *
	 * render the table to manage the nestedset
	 *
	 */
	public function render()
	{

		if  (isset($this->_options['changeparenturl'])) {

		}

		echo '<div id="nestedset-table">';
		$nbNodes = count($this->_nestedSetCollection);
		for ($i = 0 ; $i < $nbNodes ; $i ++) {
			echo '<div class="nested-node" id="node-' . $this->_nestedSetCollection[$i]->getPkValue() . '" >';
			//echo '  <div class="nested-node-handle"></div>';
			echo '  <div class="nested-node-label">';
			if  (isset($this->_options['editurl'])) {
				echo '<a href="' .
					$this->_translateUrl(
						'editurl',
						array(
							$this->_nestedSetCollection[$i]->getPkName() => $this->_nestedSetCollection[$i]->getPkValue()
						)
					) . '" alt="' .
					(
						is_array($this->_options['editurl']) ?
						$this->_options['editurl'][1] :
						'edit'
					) . '">';
			}
			echo $this->_nestedSetCollection[$i]->getLabel();

			if  (isset($this->_options['editurl'])) {
				echo '</a>';
			}

			//display options only if there are options
			echo '	  <div class="nested-node-options">';
			if ($this->_nestedSetCollection[$i]->canBeMovedToLeft()) {
				echo $this->_getMoveLeftHTML($this->_nestedSetCollection[$i]);
			}

			if ($this->_nestedSetCollection[$i]->canBeMovedToRight()) {
				echo $this->_getMoveRightHTML($this->_nestedSetCollection[$i]);
			}

			if  (isset($this->_options['changeparenturl'])) {
				echo $this->_getChangeParentHTML($this->_nestedSetCollection[$i]);
			}

			if  (isset($this->_options['deleteurl'])) {
				echo $this->_getDeleteHTML($this->_nestedSetCollection[$i]);
			}

			echo '	  </div>';
			//label closed
			echo '  </div>';
			//node children
			echo '  <div class="nested-node-children" id="node-' . $this->_nestedSetCollection[$i]->getPkValue() . '-children">';
			//close children
			//if the current is not the last
			//but he is the last of his parents, they must be closed
			if ($i < $nbNodes - 1 && $this->_nestedSetCollection[$i + 1]->getLeft() > $this->_nestedSetCollection[$i]->getRight() + 1) {
				for ($j = $this->_nestedSetCollection[$i]->getRight() ; $j < $this->_nestedSetCollection[$i + 1]->getLeft() ; $j++) {
					echo '  </div>';
					echo '</div>';
				}
			}
			//if the last has parents, they must be closed
			elseif ($i == $nbNodes - 1 && $this->_nestedSetCollection[$i]->getRight() < 2 * $nbNodes) {
				for ($j = $this->_nestedSetCollection[$i]->getRight() ; $j <= 2 * $nbNodes ; $j++) {
					echo '  </div>';
					echo '</div>';
				}
			}
			//elseif the current is the last, with no parent of if the next is his sibling, just close the current
			elseif ($i == $nbNodes - 1 || $this->_nestedSetCollection[$i + 1]->getLeft() == $this->_nestedSetCollection[$i]->getRight() + 1) {
				echo '  </div>';
				echo '</div>';
			}
		}

		echo '</div>';
	}

	protected function _translateUrl($urlName, $params = array())
	{
		$url = is_array($this->_options[$urlName]) ?
				$this->_options[$urlName][0] :
				$this->_options[$urlName];

		$url = urldecode($url);
		foreach ($params as $paramName => $paramValue) {
			$url = str_replace(':' . $paramName, $paramValue, $url);
		}

		return $url;
	}

	protected function _getChangeParentHTML($current)
	{
		$parentsList = array();
		$parent = $current->getParent();
		foreach ($this->_nestedSetCollection as $item) {
			if ($item->getPkValue() != $current->getPkValue()) {
				$directParent = $parent && $parent->getPkValue() == $item->getPkValue();

				$parentsList[$item->getPkValue()] = '<option value="' . $this->_translateUrl(
					'changeparenturl',
					array(
						$current->getPkName() => $current->getPkValue(),
						'id_parent' => $item->getPkValue(),
					)
				) . '" ' .
				($directParent ? 'selected="selected"' : '')
				 . '>' . $item->nestedset_label . '</option>';
			}
		}

		return '<select name="id_parent" onchange="window.location = this.value;">' .
			implode(
				'',
				array(
					'<option value="' . $this->_translateUrl(
					'changeparenturl',
					array(
						$current->getPkName() => $current->getPkValue(),
						'id_parent' => '',
					)
				) . '">Aucun</option>'
				) + $parentsList
			) .
			'</select>';
	}

	protected function _getDeleteHTML($current)
	{
		return '<a href="' .
			$this->_translateUrl(
				'deleteurl',
				array(
					$current->getPkName() => $current->getPkValue()
				)
			) . '" onclick="return confirm(\'Voulez-vous vraiment supprimer cet élément ?\');">' .
			'<img src="/images/delete.png" alt="' . (
				is_array($this->_options['deleteurl']) ?
				$this->_options['deleteurl'][1] :
				'delete'
			) . '" />'
			 . '</a>';
	}

	protected function _getMoveRightHTML($current)
	{
		return '<a href="' . $this->_translateUrl(
			'moveurl',
			array(
				$current->getPkName() => $current->getPkValue()
				)
			) . '?move=1' . '" alt="down">
		' .
		'<img src="/images/down.png" alt="' . (
			is_array($this->_options['moveurl']) ?
			$this->_options['moveurl'][1] :
			'down'
		) . '" />'
		 . '
		</a>';
	}

	protected function _getMoveLeftHTML($current)
	{
		return '<a href="' . $this->_translateUrl(
			'moveurl',
				array(
					$current->getPkName() => $current->getPkValue()
				)
			) . '?move=-1' . '" alt="up">
		' .
		'<img src="/images/up.png" alt="' . (
			is_array($this->_options['moveurl']) ?
			$this->_options['moveurl'][1] :
			'up'
		) . '" />'
		 . '
		</a>';
	}
}
