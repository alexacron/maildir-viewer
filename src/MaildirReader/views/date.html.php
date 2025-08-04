<?php include 'views/header.html'; ?>

<h1>Emails received on <?php echo date('Y-m-d', strtotime($date)); ?></h1>

<?php foreach ($allMails as $mail) { ?>
    <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <p><a href="view?id=<?php echo $mail['id']; ?>"><?php echo $mail['subject']; ?></a></p>
        <p><strong>From:</strong> <?php echo $mail['from']; ?> <small>(<?php echo $mail['date']; ?>)</small></p>
        <div style="padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <?php echo $mail['html']; ?>
        </div>
    </div>
<?php
}
?>
<?php include 'views/footer.html'; ?>
