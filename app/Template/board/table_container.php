<div id="board-container">
    <?php if (isset($not_editable)): ?>
        <table id="board" class="board-project-<?= $project['id'] ?>">
    <?php else: ?>
        <table id="board"
               class="board-project-<?= $project['id'] ?>"
               data-project-id="<?= $project['id'] ?>"
               data-check-interval="<?= $board_private_refresh_interval ?>"
               data-save-url="<?= $this->url->href('board', 'save', array('project_id' => $project['id'])) ?>"
               data-check-url="<?= $this->url->href('board', 'check', array('project_id' => $project['id'], 'timestamp' => time())) ?>"
               data-task-creation-url="<?= $this->url->href('task', 'create', array('project_id' => $project['id'])) ?>"
        >
    <?php endif ?>

    <?php foreach ($swimlanes as $swimlane): ?>
        <?php if (empty($swimlane['columns'])): ?>
            <p class="alert alert-error"><?= t('There is no column in your project!') ?></p>
            <?php break ?>
        <?php else: ?>
            <?= $this->render('board/table_swimlane', array(
                'project' => $project,
                'swimlane' => $swimlane,
                'board_highlight_period' => $board_highlight_period,
                'hide_swimlane' => count($swimlanes) === 1,
                'not_editable' => isset($not_editable),
            )) ?>
        <?php endif ?>
    <?php endforeach ?>
    </table>
</div>