<form action="/apis/signup" method="post" enctype="multipart/form-data">
	<input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Resume" name="submit">
  </form>
<p>Here is a list of all apis:</p>

<?php foreach($apis as $api) { ?>
  <p>
    <?php echo $api->author; ?>
    <a href='?controller=apis&action=show&id=<?php echo $api->id; ?>'>See content</a>
  </p>
<?php } ?>