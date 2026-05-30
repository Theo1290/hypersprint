<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

start_session();
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// GET - return feed posts with like counts
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 9;
    $offset = ($page - 1) * $limit;

    try {
        $stmt = $pdo->prepare("
            SELECT
                fp.id as post_id,
                fp.post_text,
                fp.created_at,
                u.username,
                u.profile_url,
                COUNT(fl.id) as like_count,
                MAX(CASE WHEN fl.user_id = ? THEN 1 ELSE 0 END) as liked_by_me
            FROM feed_posts fp
            JOIN users u ON u.id = fp.user_id
            LEFT JOIN feed_likes fl ON fl.post_id = fp.id
            GROUP BY fp.id, fp.post_text, fp.created_at, u.username, u.profile_url
            ORDER BY fp.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) { //'&post' means we are modifyig the actual array element.
            $post['post_data'] = json_decode($post['post_text'], true);
            $post['like_count'] = (int)$post['like_count'];
            $post['liked_by_me'] = (bool)$post['liked_by_me'];
        }

        // Get toal for pagination
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM feed_posts");
        $total = (int)$stmt->fetch()['total'];

        send_json([
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total /$limit)
        ]);

    } catch (PDOException $e) {
        send_json(['error' => 'Failed to load feed'], 500);
    }
}


// POST - like or unlike a post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user_id) {
        send_json(['error' => 'Authentication required'], 401);
    }

    $data = get_json_body();
    $post_id = isset($data['post_id']) ? (int)$data['post_id'] : 0;

    if (!$post_id) {
        send_json(['error' => 'Missing post_id'], 400);
    }

    try {
        // Check if already liked
        $stmt = $pdo->prepare("
            SELECT id FROM feed_likes WHERE post_id = ? AND user_id = ? 
        ");
        $stmt->execute([$post_id, $user_id]);
        $existing = $stmt->fetch();

        if($existing) {
            // unlike
            $stmt = $pdo->prepare("DELETE FROM feed_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            send_json(['success' => true, 'action' => 'unliked']);
        } else {
            // like
            $stmt = $pdo->prepare("INSERT INTO feed_likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $user_id]);
            send_json(['success' => true, 'action' => 'liked']);
        }

    } catch (PDOException $e) {
        send_json(['error' => 'Failed to process like'], 500);
    }
}