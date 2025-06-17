<?php
// Simpan sebagai test_post.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Post Form</title>
</head>
<body>
    <h2>Test Form</h2>
    <form action="tambah_postingan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="kelas_id" value="63">
        <textarea name="konten">Test konten</textarea>
        <br><br>
        <input type="file" name="lampiran[]" multiple>
        <br><br>
        <button type="submit">Submit Test</button>
    </form>
</body>
</html>