<?php
session_start();

require_once 'koneksi.php';
require_once 'ai_analysis.php';

$report = generateAIReport($koneksi, $_SESSION['userid'], $_SESSION['level']);

$systemMessage = [
    'role' => 'system',
    'content' => "You are SMAGA AI, assisting " . $report['user_info']['name'] . 
                 " who is a " . $report['user_info']['level'] . 
                 " at SMAGA. Their common topics of interest are: " . 
                 implode(', ', array_keys($report['conversation_analysis']['common_topics'])) .
                 ". Their character traits indicate: " . 
                 implode(', ', array_map(function($k, $v) { 
                     return "$k: " . number_format($v, 2); 
                 }, array_keys($report['conversation_analysis']['character_traits']), 
                    $report['conversation_analysis']['character_traits'])) .
                 ". Adjust responses accordingly.",
];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">AI Interaction Report</h5>
        <span class="text-muted" style="font-size: 0.8rem">Last Updated: <?php echo date('d M Y H:i'); ?></span>
    </div>
    <div class="card-body">
        <!-- User Profile Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-person-circle me-2"></i>User Profile</h6>
                        <div class="mt-3">
                            <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($report['user_info']['name']); ?></p>
                            <p class="mb-2"><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($report['user_info']['level'])); ?></p>
                            <p class="mb-0"><strong>Total Interactions:</strong> <?php echo $report['user_info']['total_conversations']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-graph-up me-2"></i>Interaction Patterns</h6>
                        <div class="mt-3">
                            <p class="mb-2"><strong>Avg Message Length:</strong> <?php echo $report['conversation_analysis']['interaction_patterns']['avg_message_length']; ?> chars</p>
                            <p class="mb-2"><strong>Questions Asked:</strong> <?php echo $report['conversation_analysis']['interaction_patterns']['question_frequency']; ?></p>
                            <p class="mb-0"><strong>Last Active:</strong> <?php echo $report['conversation_analysis']['last_interaction'] ? date('d M Y H:i', strtotime($report['conversation_analysis']['last_interaction'])) : 'Never'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Topics & Traits Section -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <h6><i class="bi bi-chat-square-text me-2"></i>Common Topics</h6>
                <?php foreach ($report['conversation_analysis']['common_topics'] as $topic => $count): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span><?php echo ucfirst(htmlspecialchars($topic)); ?></span>
                        <span class="text-muted"><?php echo $count; ?> mentions</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar color-web" role="progressbar" 
                             style="width: <?php echo ($count / array_sum($report['conversation_analysis']['common_topics']) * 100); ?>%">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-md-6 mb-4">
                <h6><i class="bi bi-person-badge me-2"></i>Character Traits</h6>
                <?php if (!empty($report['conversation_analysis']['character_traits'])): ?>
                    <?php foreach ($report['conversation_analysis']['character_traits'] as $trait => $value): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo ucfirst(htmlspecialchars($trait)); ?></span>
                            <span class="text-muted"><?php echo number_format($value, 2); ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar color-web" role="progressbar" 
                                 style="width: <?php echo min($value * 100, 100); ?>%">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No character traits analyzed yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.color-web {
    background-color: rgb(218, 119, 86);
}
.progress-bar {
    transition: width 0.5s ease-in-out;
}
</style>