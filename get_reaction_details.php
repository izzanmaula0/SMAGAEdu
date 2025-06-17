<?php
require 'koneksi.php';

$comment_id = $_GET['comment_id'];
$query = "SELECT cr.emoji, g.namaLengkap, g.foto_profil, 'guru' as user_type, cr.created_at 
          FROM comment_reactions cr 
          JOIN guru g ON cr.user_id = g.username 
          WHERE cr.comment_id = ?
          UNION ALL
          SELECT cr.emoji, s.nama as namaLengkap, s.foto_profil, 'siswa' as user_type, cr.created_at
          FROM comment_reactions cr 
          JOIN siswa s ON cr.user_id = s.username 
          WHERE cr.comment_id = ?
          ORDER BY created_at DESC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ii", $comment_id, $comment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reactions = [];
while($row = mysqli_fetch_assoc($result)) {
    $reactions[$row['emoji']][] = $row;
}

uasort($reactions, function($a, $b) {
    return count($b) - count($a);
});
?>

<div class="reaction-container" style="max-height: 200px; overflow-y: auto; font-size: 0.9em;">
    <?php foreach($reactions as $emoji => $users): ?>
        <div class="d-flex align-items-center mb-2">
            <div class="me-2">
                <?php echo $emoji; ?> 
            </div>
            <div class="d-flex flex-wrap align-items-center gap-1">
                <?php foreach($users as $user): ?>
                    <img src="<?php echo !empty($user['foto_profil']) ? 'uploads/profil/'.$user['foto_profil'] : 'assets/pp.png'; ?>" 
                         alt="<?php echo htmlspecialchars($user['namaLengkap']); ?>"
                         title="<?php echo htmlspecialchars($user['namaLengkap']); ?>"
                         class="rounded-circle"
                         style="width: 24px; height: 24px; object-fit: cover;">
                    <p class="p-0 m-0" style="font-size: 10px;"><?php echo htmlspecialchars($user['namaLengkap']); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>