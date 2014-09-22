<?php
namespace KayStrobach\Themes\ViewHelpers\Iterator;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 * Class SortViewHelper
 *
 * Sorts a given array by a given key
 *
 * @package KayStrobach\Themes\ViewHelpers\Iterator
 */
class SortViewHelper extends AbstractViewHelper {

	/**
	 * @param array $subject
	 * @param string $key
	 * @return array|null
	 */
	public function render($subject = NULL, $key = 'label') {
		$this->arguments['key'] = $key;
		if (NULL === $subject) {
			$subject = $this->renderChildren();
		}
		$sorted = NULL;
		if (TRUE === is_array($subject)) {
			$sorted = $this->sortArray($subject);
		}
		return $sorted;
	}

	/**
	 * Sort an array
	 *
	 * @param array $array
	 * @return array
	 */
	protected function sortArray($array) {
		usort($array, array($this, 'compare'));
		return $array;
	}


	public function compare($a, $b) {
		return strcasecmp($a[$this->arguments['key']], $b[$this->arguments['key']]);
	}

}
