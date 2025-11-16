<?php if (isset($pageHeader)): ?>
<div class="page-header mb-4">
  <div class="row align-items-center">
    <div class="col">
      <h1 class="page-header-title"><?php echo $pageHeader; ?></h1>
      <?php if (isset($pageDescription)): ?>
      <p class="page-header-text"><?php echo $pageDescription; ?></p>
      <?php endif; ?>
    </div>
    <?php if (isset($pageActions)): ?>
    <div class="col-auto">
      <?php echo $pageActions; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if (isset($breadcrumbs)): ?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="/it_smo/pages/dashboard/index.php">
        <i class="fas fa-home"></i>
      </a>
    </li>
    <?php foreach ($breadcrumbs as $breadcrumb): ?>
    <?php if ($breadcrumb['active']): ?>
    <li class="breadcrumb-item active" aria-current="page"><?php echo $breadcrumb['text']; ?></li>
    <?php else: ?>
    <li class="breadcrumb-item">
      <a href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['text']; ?></a>
    </li>
    <?php endif; ?>
    <?php endforeach; ?>
  </ol>
</nav>
<?php endif; ?>