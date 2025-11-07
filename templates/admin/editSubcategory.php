<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

<h1><?php echo htmlspecialchars($results['pageTitle'] ?? ''); ?></h1>

<form action="admin.php?action=<?php echo htmlspecialchars($results['formAction'] ?? ''); ?>" method="post">
  <!-- Обработка формы будет направлена файлу admin.php 
       (функции newSubcategory либо editSubcategory в зависимости от formAction) -->

  <input type="hidden" name="subcategoryId" 
         value="<?php echo htmlspecialchars($results['subcategory']->id ?? ''); ?>" />

  <?php if (!empty($results['errorMessage'])) { ?>
    <div class="errorMessage"><?php echo htmlspecialchars($results['errorMessage']); ?></div>
  <?php } ?>

  <ul>
    <li>
      <label for="name">Subcategory Name</label>
      <input type="text" name="name" id="name"
             placeholder="Name of the subcategory"
             required autofocus maxlength="255"
             value="<?php echo htmlspecialchars($results['subcategory']->name ?? ''); ?>" />
    </li>

    <li>
      <label for="category_id">Category</label>
      <select name="category_id" id="category_id" required>
        <option value="">Select a Category</option>
        <?php foreach ($results['categories'] as $category) { ?>
          <option value="<?php echo htmlspecialchars($category->id); ?>"
            <?php echo (!empty($results['subcategory']->category_id) && $category->id == $results['subcategory']->category_id) ? "selected" : ""; ?>>
            <?php echo htmlspecialchars($category->name); ?>
          </option>
        <?php } ?>
      </select>
    </li>

    <li>
      <label for="description">Description</label>
      <textarea name="description" id="description"
                placeholder="Brief description of the subcategory"
                maxlength="1000" style="height: 5em;"><?php 
                echo htmlspecialchars($results['subcategory']->description ?? ''); ?></textarea>
    </li>
  </ul>

  <div class="buttons">
    <input type="submit" name="saveChanges" value="Save Changes" />
    <input type="submit" formnovalidate name="cancel" value="Cancel" />
  </div>
</form>

<?php if (!empty($results['subcategory']->id)) { ?>
  <p>
    <a href="admin.php?action=deleteSubcategory&amp;subcategoryId=<?php echo htmlspecialchars($results['subcategory']->id); ?>"
       onclick="return confirm('Delete This Subcategory?')">Delete This Subcategory</a>
  </p>
<?php } ?>

<?php include "templates/include/footer.php" ?>
