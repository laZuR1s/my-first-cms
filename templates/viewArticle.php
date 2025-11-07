<?php include "templates/include/header.php" ?>
  
<h1><?php echo htmlspecialchars($results['article']->title ?? ''); ?></h1>

<p>
    Publication date: <?php echo date('j F Y', $results['article']->publicationDate ?? time()); ?>
    <?php 
    // Категория
    if (!empty($results['article']->categoryId) && !empty($results['categories'][$results['article']->categoryId])) {
        $category = $results['categories'][$results['article']->categoryId];
        echo ' | Category: <a href=".?action=archive&amp;categoryId=' . $results['article']->categoryId . '">'
             . htmlspecialchars($category->name ?? '') . '</a>';
    }

    // Подкатегория
    if (!empty($results['article']->subcategory_id)) {
        $subcategory = Subcategory::getById($results['article']->subcategory_id);
        echo ' | Subcategory: <a href=".?action=archiveBySubcategory&amp;subcategoryId=' . $results['article']->subcategory_id . '">'
             . ($subcategory ? htmlspecialchars($subcategory->name ?? '') : 'Unknown') . '</a>';
    }

    // Авторы
    $authors = Article::getArticleAuthors($results['article']->id);
    if (!empty($authors)) {
        $authorNames = array_map(fn($a) => $a->username, $authors);
        echo ' | Authors: ' . htmlspecialchars(implode(', ', $authorNames));
    }
    ?>
</p>

<div class="article-content">
    <?php echo htmlspecialchars($results['article']->content ?? ''); ?>
</div>

<p><a href="./">Return to Homepage</a></p>

<?php include "templates/include/footer.php" ?>
