<?php

/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /** @var int ID статьи */
    public $id = null;

    /** @var int Дата первой публикации статьи */
    public $publicationDate = null;

    /** @var string Полное название статьи */
    public $title = null;

    /** @var int ID категории статьи */
    public $categoryId = null;

    /** @var string Краткое описание статьи */
    public $summary = null;

    /** @var string HTML содержание статьи */
    public $content = null;

    /** @var int Активность статьи (1 — активна, 0 — скрыта) */
    public $active = 1;


    /**
     * Создаёт объект статьи
     * @param array $data массив значений строки таблицы
     */
    public function __construct($data = array())
    {
        if (isset($data['id'])) $this->id = (int) $data['id'];
        if (isset($data['publicationDate'])) $this->publicationDate = (string) $data['publicationDate'];
        if (isset($data['title'])) $this->title = $data['title'];
        if (isset($data['categoryId'])) $this->categoryId = (int) $data['categoryId'];
        if (isset($data['summary'])) $this->summary = $data['summary'];
        if (isset($data['content'])) $this->content = $data['content'];
        if (isset($data['active'])) $this->active = (int) $data['active']; // 👈 новое поле
    }


    /**
     * Устанавливаем свойства из формы
     */
    public function storeFormValues($params)
    {
        $this->__construct($params);

        if (isset($params['publicationDate'])) {
            $publicationDate = explode('-', $params['publicationDate']);
            if (count($publicationDate) == 3) {
                list($y, $m, $d) = $publicationDate;
                $this->publicationDate = mktime(0, 0, 0, $m, $d, $y);
            }
        }

        // 👇 сохраняем активность (чекбокс в форме)
        $this->active = isset($params['active']) ? 1 : 0;
    }


    /**
     * Получить статью по ID
     */
    public static function getById($id)
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate 
                FROM articles WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;

        return $row ? new Article($row) : false;
    }


    /**
     * Получить список статей
     */
public static function getList($numRows = 1000000, $categoryId = null, $order = "publicationDate DESC", $onlyActive = false)
    {
     $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $fromPart = "FROM articles";

    // Формируем условия WHERE
    $whereClauses = array();
    if ($categoryId) $whereClauses[] = "categoryId = :categoryId";
    if ($onlyActive) $whereClauses[] = "active = 1";

    $where = count($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

    // Запрос для выборки статей
    $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate
            $fromPart $where
            ORDER BY $order
            LIMIT :numRows";

    $st = $conn->prepare($sql);
    $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
    if ($categoryId) $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
    $st->execute();

    $list = array();
    while ($row = $st->fetch()) {
        $list[] = new Article($row);
    }

    // Считаем общее количество
    $sql = "SELECT COUNT(*) AS totalRows $fromPart $where";
    $st = $conn->prepare($sql);
    if ($categoryId) $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
    $st->execute();
    $totalRows = $st->fetch();

    $conn = null;

    return array(
        "results" => $list,
        "totalRows" => $totalRows[0]
    );
    }


    /**
     * Вставить новую статью
     */
    public function insert()
    {
        if (!is_null($this->id))
            trigger_error("Article::insert(): попытка вставить статью с уже установленным ID ($this->id)", E_USER_ERROR);

        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO articles 
                (publicationDate, categoryId, title, summary, content, active)
                VALUES (FROM_UNIXTIME(:publicationDate), :categoryId, :title, :summary, :content, :active)";
        $st = $conn->prepare($sql);
        $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->bindValue(":title", $this->title, PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }


    /**
     * Обновить статью
     */
    public function update()
    {
        if (is_null($this->id))
            trigger_error("Article::update(): попытка обновить статью без ID", E_USER_ERROR);

        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE articles SET 
                publicationDate=FROM_UNIXTIME(:publicationDate),
                categoryId=:categoryId,
                title=:title,
                summary=:summary,
                content=:content,
                active=:active
                WHERE id = :id";

        $st = $conn->prepare($sql);
        $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->bindValue(":title", $this->title, PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }


    /**
     * Удалить статью
     */
    public function delete()
    {
        if (is_null($this->id))
            trigger_error("Article::delete(): попытка удалить статью без ID", E_USER_ERROR);

        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $st = $conn->prepare("DELETE FROM articles WHERE id = :id LIMIT 1");
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }
}
