<?php
/**
 * Error class
 * @author Adler Brediks Medrado
 * @email adler@neshertech.net
 * @copyright 2005 - Nesher Technologies - www.neshertech.net
 * @date 30/11/2005
 */
class Error extends Exception {

	private $codSQLState;
	private $codDriverError;
	private $msgError;
	private $isOk;

	/**
	 * Verify if exists a error.
	 *
	 * @param array $arError
	 */
	public function __construct($arError=null) {

		if (!is_null($arError)) {
			if (count($arError)==3) {
				$this->errorOcurred($arError);
			}
		}
	}

	public function getCodSQLState() {
		return $this->codSQLState;
	}
	public function setCodSQLState($codSqlState) {
		$this->codSQLState = $codSqlState;
	}
	public function getCodDriverError() {
		return $this->codDriverError;
	}
	public function setCodDriverError($codDriverError) {
		$this->codDriverError = $codDriverError;
	}
	public function getMsgError() {
		return $this->msgError;
	}
	public function setMsgError($msgError) {
		$this->msgError = $msgError;
	}

	/**
	 * If a error occurs the object will be informed
	 *
	 * @param array $arErrors
	 */
	public function errorOcurred($arErrors) {
		$this->setCodSQLState($arErrors[0]);
		$this->setCodDriverError($arErrors[1]);
		$this->setMsgError($arErrors[2]);
	}

}
?>