<?php
/** Shared helper functions */

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function initials_from_name($name) {
    $parts = preg_split('/\s+/', trim($name));
    $parts = array_filter($parts);
    if (count($parts) === 0) return '?';
    if (count($parts) === 1) return strtoupper(substr($parts[0], 0, 2));
    return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}

/** Compute % completion (out of 6 steps: 5 sections + quiz) for a topic's progress row. */
function topic_percent($progressRow) {
    if (!$progressRow) return 0;
    $keys = ['overview_done', 'symptoms_done', 'causes_done', 'prevention_done', 'vaccination_done'];
    $done = 0;
    foreach ($keys as $k) if (!empty($progressRow[$k])) $done++;
    if (!empty($progressRow['quiz_done'])) $done++;
    return (int) round(($done / 6) * 100);
}

/** Get (or lazily create) a learning_progress row for a user+topic. */
function get_or_create_progress($userId, $topic) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM learning_progress WHERE user_id = ? AND topic = ?');
    $stmt->execute([$userId, $topic]);
    $row = $stmt->fetch();
    if (!$row) {
        $ins = $pdo->prepare('INSERT INTO learning_progress (user_id, topic) VALUES (?, ?)');
        $ins->execute([$userId, $topic]);
        $stmt->execute([$userId, $topic]);
        $row = $stmt->fetch();
    }
    return $row;
}

/** Determine the next incomplete lesson step across HPV then HSV, for the dashboard "Today's Goal" card. */
function find_next_goal($userId) {
    $order = ['overview', 'symptoms', 'causes', 'prevention', 'vaccination', 'quiz'];
    $labels = ['hpv' => 'HPV', 'hsv' => 'HSV'];
    foreach (['hpv', 'hsv'] as $topic) {
        $progress = get_or_create_progress($userId, $topic);
        foreach ($order as $section) {
            $doneKey = $section === 'quiz' ? 'quiz_done' : $section . '_done';
            if (empty($progress[$doneKey])) {
                $stepTitle = $section === 'quiz' ? 'Quiz' : ucfirst($section);
                return [
                    'topic' => $topic,
                    'section' => $section,
                    'title' => $section === 'overview' ? "Learn {$labels[$topic]} Basics" : "Continue: {$labels[$topic]} — {$stepTitle}",
                    'desc' => $section === 'quiz'
                        ? "Test what you've learned about {$labels[$topic]} with a short quiz."
                        : "Next up in {$labels[$topic]}: " . strtolower($stepTitle) . '.',
                ];
            }
        }
    }
    return ['topic' => null, 'section' => null, 'title' => "You're all caught up! 🎉", 'desc' => 'Both lessons are complete. Try the Symptom Checker or book a screening.'];
}

function overall_percent($userId) {
    $hpv = topic_percent(get_or_create_progress($userId, 'hpv'));
    $hsv = topic_percent(get_or_create_progress($userId, 'hsv'));
    return (int) round(($hpv + $hsv) / 2);
}

function flash_message_html() {
    $html = '';
    if ($err = flash_get('flash_error')) {
        $html .= '<div class="flash flash-error"><i class="fas fa-triangle-exclamation"></i> ' . e($err) . '</div>';
    }
    if ($errHtml = flash_get('flash_error_html')) {
        // Trusted, server-built HTML only (never raw user input) — used when a flash message needs an inline link.
        $html .= '<div class="flash flash-error"><i class="fas fa-triangle-exclamation"></i> ' . $errHtml . '</div>';
    }
    if ($ok = flash_get('flash_success')) {
        $html .= '<div class="flash flash-success"><i class="fas fa-circle-check"></i> ' . e($ok) . '</div>';
    }
    return $html;
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
