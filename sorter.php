<?php
/*
W-PHP Sorter
=====================
File: sorter.php
Author: Ali Candan [Webkolog] <webkolog@gmail.com> 
Homepage: http://webkolog.net
GitHub Repo: https://github.com/webkolog/php-sorter
Last Modified: 2016-09-24
Created Date: 2015-06-03
Compatibility: PHP 5.4+
@version 1.0

Copyright (C) 2015 Ali Candan
Licensed under the MIT license http://mit-license.org

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class Sorter {
	
	public $tableName = null;
	public $listRow = null;
	public $filter = null;
	public $filterValues = array();
	private $filterString = null;
	public $idColName = "id";
	private $db;
	private $config = false;
	private $isFilter = false;
	private $errorMessage = null;
	private $language = 'en'; //Default language
    private $languageData = array();

	public function __construct($db, $tableName = null, $listRow = null, $filter = null, $filterValues = array(), $language = 'en') {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->listRow = $listRow;
        $this->filter = $filter;
        $this->filterValues = $filterValues;
        $this->setLanguage($language);
    }
	
	public function setLanguage($language) {
        $this->language = $language;
        $this->loadLanguageFile();
    }

    private function loadLanguageFile() {
        $languageFilePath = __DIR__ . '/language/' . $this->language . '.php';
        if (file_exists($languageFilePath)) {
            $this->languageData = include $languageFilePath;
        } else {
            $languageFilePath = __DIR__ . '/language/en.php';
            if (file_exists($languageFilePath)) {
                $this->languageData = include $languageFilePath;
            } else {
                $this->languageData = array(
                    'already_first' => 'Already at the beginning!',
                    'already_last' => 'Already at the end!',
                    'same_selection' => 'Selected items are the same!',
                    'record_not_found' => 'Record not found!'
                );
            }
        }
    }

    private function getLanguageMessage($key) {
        return isset($this->languageData[$key]) ? $this->languageData[$key] : $key;
    }

	private function setConfig() {
		if (!$this->config) {
			if (strlen($this->filter) > 0) {
				$this->filterString = "WHERE ".$this->filter;
				$this->isFilter = true;
			}
			$this->config = true;
		}
	}

	private function setWhere($data) {
		if ($this->isFilter)
			$where = " AND " . $data;
		else
			$where = "WHERE " . $data;
		return $where;
	}
	
	public function resetFilter() {
		$this->config = false;
	}

	public function moveToFirst($data_id) {
		$this->setConfig();
		$situation = false;
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$rs = $sth->fetch(PDO::FETCH_NUM);
		$order_no = $rs[0];
		if ($order_no > 1) {
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . " = " . $this->listRow . " + 1 " . $this->filterString . $this->setWhere($this->idColName." <> :vi_id AND " . $this->listRow . " < :vi_" . $this->listRow));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->bindValue(":vi_" . $this->listRow, $order_no);
			$sth->execute();
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . " = 1 " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->execute();
			$situation = true;
		} else {
			$this->errorMessage = $this->getLanguageMessage('already_first');
		}
		return $situation;
	}

	public function moveToLast($data_id) {
		$this->setConfig();
		$situation = false;
		$sth = $this->db->prepare("SELECT MAX(" . $this->listRow . ") FROM " . $this->tableName . " " . $this->filterString);
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->execute();
		$rs = $sth->fetch(PDO::FETCH_NUM);
		$last_order_no = $rs[0];
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$rs = $sth->fetch(PDO::FETCH_NUM);
		$order_no = $rs[0];
		if ($order_no < $last_order_no) {
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=" . $this->listRow . "-1 " . $this->filterString . $this->setWhere($this->idColName." <> :vi_id AND " . $this->listRow . " > :vi_" . $this->listRow));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->bindValue(":vi_" . $this->listRow, $order_no);
			$sth->execute();
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=$last_order_no " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->execute();
			$situation = true;
		} else {
			$this->errorMessage = $this->getLanguageMessage('already_last');
		}
		return $situation;
	}

	public function moveAfter($data_id, $target_id) {
		$this->setConfig();
		$situation = false;
		$target_order_no = $this->getOrderNum($target_id);
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName . " = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$order_no = $sth->fetchColumn();
		if ($target_order_no == $order_no) {
			$this->errorMessage = $this->getLanguageMessage('same_selection');
			return $situation;
		} else if ($target_order_no < $order_no) {
			$target_order_no++;
		}
		$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . " = '0' ".$this->filterString.$this->setWhere($this->idColName . " = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . " = " . $this->listRow . " -1 ".$this->filterString.$this->setWhere($this->listRow." >= :order_no"));
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":order_no", $order_no);
		$sth->execute();
		$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . " = " . $this->listRow . " +1 ".$this->filterString.$this->setWhere($this->listRow . " >= :target_order_no"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":target_order_no", $target_order_no);
		$sth->execute();
		$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . " = :target_order_no ".$this->filterString.$this->setWhere($this->idColName . " = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->bindValue(":target_order_no", $target_order_no);
		$sth->execute();
		$situation = true;
		return $situation;
	}

	public function moveUp($data_id) {
		$this->setConfig();
		$situation = false;
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$order_no = $sth->fetchColumn();
		if ($order_no > 1) {
			$target_order_no = $order_no - 1;
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=$order_no " . $this->filterString . $this->setWhere($this->listRow . "=" . $target_order_no));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->execute();
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=" . $this->listRow . "-1 " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->execute();
			$situation = true;
		} else {
			$this->errorMessage = $this->getLanguageMessage('already_first');
		}
		return $situation;
	}

	public function moveDown($data_id) {
		$this->setConfig();
		$situation = false;
		$sth = $this->db->prepare("SELECT MAX(" . $this->listRow . ") FROM " . $this->tableName . " " . $this->filterString);
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->execute();
		$rs = $sth->fetch(PDO::FETCH_NUM);
		$last_order_no = $rs[0];
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$order_no = $sth->fetchColumn();
		if ($order_no < $last_order_no) {
			$target_order_no = $order_no + 1;
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=$order_no " . $this->filterString . $this->setWhere($this->listRow . "=" . $target_order_no));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->execute();
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=" . $this->listRow . "+1 " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->execute();
			$situation = true;
		} else {
			$this->errorMessage = $this->getLanguageMessage('already_last');
		}
		return $situation;
	}

	public function exchange($data_id, $target_id) {
		$this->setConfig();
		$situation = false;
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$order_no = $sth->fetchColumn();
		$target_order_no = $this->getOrderNum($target_id);
		if ($order_no != $target_order_no) {
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=$target_order_no " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->execute();
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=$order_no " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $target_id);
			$sth->execute();
			$situation = true;
		} else {
			$this->errorMessage = $this->getLanguageMessage('same_selection');
		}
		return $situation;
	}

	public function delete($data_id) {
		$this->setConfig();
		$situation = false;
		$sth = $this->db->prepare("SELECT " . $this->listRow . " FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$rs = $sth->fetch(PDO::FETCH_NUM);
		$order_no = $rs[0];
		if ($order_no != null) {
			$sth = $this->db->prepare("DELETE FROM " . $this->tableName . " " . $this->filterString . $this->setWhere($this->idColName." = :vi_id"));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->bindValue(":vi_id", $data_id);
			$sth->execute();
			$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=" . $this->listRow . "-1 " . $this->filterString . $this->setWhere($this->listRow . " > " . $order_no));
			foreach ($this->filterValues as $key => $value) {
				$new_key = is_numeric($key) ? $key + 1 : $key;
				$sth->bindValue($new_key, $value);
			}
			$sth->execute();
			$situation = true;
		} else {
			$this->errorMessage = $this->getLanguageMessage('record_not_found');
		}
		return $situation;
	}

	public function getFreeListNo() {
		$this->setConfig();
		$sth = $this->db->prepare("SELECT MAX(" . $this->listRow . ") FROM " . $this->tableName . " " . $this->filterString);
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->execute();
		$order_no = $sth->fetchColumn();
		if ($order_no == null) {
			$order_no = 1;
		} else {
			$order_no++;
		}
		return $order_no;
	}
	
	public function reorderWithIgnoreId($data_id, $filterString, $filterValues) {
		$this->setConfig();
		$sth = $this->db->prepare("SELECT ".$this->listRow." FROM ".$this->tableName." WHERE ".$this->idColName." = :vi_id");
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		$order_no = $sth->fetchColumn();
		$sth = $this->db->prepare("UPDATE " . $this->tableName . " SET " . $this->listRow . "=" . $this->listRow . "-1 WHERE ". $this->listRow . " > '". $order_no."' AND ".$filterString);
		foreach ($filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		return $sth->execute();
	}
	
	public function getOrderNum($data_id) {
		$this->setConfig();
		$sth = $this->db->prepare("SELECT ".$this->listRow." FROM ".$this->tableName." WHERE ".$this->idColName." = :vi_id");
		$sth->bindValue(":vi_id", $data_id);
		$sth->execute();
		return $sth->fetchColumn();
	}
	
	public function getErrorMessage() {
		return $this->errorMessage;
	}
}
