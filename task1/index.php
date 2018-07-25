<?php

/**
 *  Для проверки
 */
const USERNAME = "root";
const PASSWORD = "2ZANGUaj";
const DBNAME = "test_base";

class Catalog
{
    private $db = null;

    private $category = null;

    public function __construct()
    {
        $this->db = new PDO('mysql:host=localhost;dbname='.DBNAME, USERNAME, PASSWORD);
        $stmt = $this->db->prepare('SET NAMES utf8');
        $stmt->execute();
        $this->category = $this->getMenuItems();
    }

    public function printMenu($id = false, $parent_id = false, $list = "")
    {
        $prep = $this->category;
        if ($id !== false && isset($prep["PARENT"][$id]) && is_array($prep["PARENT"][$id])) {
            $result = "<ul>";
            foreach ($prep["PARENT"][$id] as $item) {
                if ($item->id == $parent_id)
                    $result .= "<li><a href='?id={$item->id}'>{$item->name}</a> $list</li>";
                else
                    $result .= "<li><a href='?id={$item->id}'>{$item->name}</a></li>";
            }
            $result .= "</ul>";
        }

        if (isset($prep["ELEMENT"][$id]))
            $this->printMenu($prep["ELEMENT"][$id]->id_parent, $id, $result);
        else
            echo $result;
    }

    public function printTovars($category)
    {
        $cat = $this->prepareParamTovars($category);
        if (!empty($cat)) {
            foreach ($this->getTovars($cat) as $item) {
                echo "<div>{$item->name}</div>";
            }
        }
    }

    private function prepareParamTovars($category)
    {
        $cat = [];
        if (isset($this->category["PARENT"][$category])) {
            foreach ($this->category["PARENT"][$category] as $item) {
                $cat[] = $item->id;
                $cat = array_merge($cat, $this->prepareParamTovars($item->id));
            }
            return $cat;
        }
        return [$category];
    }

    private function getTovars($cat)
    {
        $stmt = $this->db->prepare('SELECT * FROM products where id_group in (' . implode(",", $cat) . ')');
        $stmt->execute();
        $prep = $stmt->fetchAll(PDO::FETCH_OBJ);
        $result = [];
        foreach ($prep as $value) {
            $result[$value->id] = $value;
        }
        return $result;
    }

    private function getMenuItems()
    {
        $stmt = $this->db->prepare('SELECT * FROM groups');
        $stmt->execute();
        $prep = $stmt->fetchAll(PDO::FETCH_OBJ);
        $result = [
            "PARENT" => [],
            "ELEMENT" => []
        ];
        foreach ($prep as $value) {
            $result["PARENT"][$value->id_parent][] = $value;
            $result["ELEMENT"][$value->id] = $value;
        }
        return $result;
    }
}

$m = new Catalog();
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hello, world!</title>
</head>
<body>
<div style="display: flex; justify-content: space-around">
    <div id="test">
        <td><?= $m->printMenu(intval($_GET['id']), false) ?></td>
    </div>
    <div>
        <td><?= $m->printTovars(intval($_GET['id'])) ?></td>
    </div>
</div>
</body>
</html>
