<?php include 'views/header.html'; ?>

<h1>View</h1>

<nav>
<ul class="pagination">
    <li class="page-item"><a href="index">Back</a></li>
    <li class="page-item"><?php if ($next) { ?>
    <a href="?id=<?php echo $next; ?>">Next</a>
        <?php } else { echo 'Next'; } ?></li>

    <li class="page-item"><?php if ($prev) { ?>
    <a href="?id=<?php echo $prev; ?>">Previous</a>
        <?php } else { echo 'Previous'; } ?></li>
</ul>
</nav>

<p><strong>Subject:</strong> <?php echo $mail['subject']; ?></p>
<p><strong>From:</strong> <?php echo $mail['from']; ?> <small>(<?php echo $mail['date']; ?>)</small></p>
<p><strong>Body:</strong></p>
<div style="padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
    <?php echo $mail['html']; ?>
</div>

<?php include 'views/footer.html'; ?>
