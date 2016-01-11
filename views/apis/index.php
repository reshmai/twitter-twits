<p>Here is a list of all apis:</p>

<?php foreach($apis as $api) { ?>
  <p>
    <?php echo $api->author; ?>
    <a href='?controller=apis&action=show&id=<?php echo $api->id; ?>'>See content</a>
  </p>
<?php } ?>