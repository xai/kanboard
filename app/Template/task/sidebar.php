<div class="sidebar">
    <h2><?= t('Information') ?></h2>
    <ul>
        <li>
            <?= $this->url->link(t('Summary'), 'task', 'show', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Activity stream'), 'activity', 'task', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Transitions'), 'task', 'transitions', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Analytics'), 'task', 'analytics', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <?php if ($task['time_estimated'] > 0 || $task['time_spent'] > 0): ?>
        <li>
            <?= $this->url->link(t('Time tracking'), 'task', 'timesheet', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <?php endif ?>
    </ul>
    <h2><?= t('Actions') ?></h2>
    <ul>
        <li>
            <?= $this->url->link(t('Edit the task'), 'task', 'edit', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Edit the description'), 'task', 'description', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Edit recurrence'), 'task', 'recurrence', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Add a sub-task'), 'subtask', 'create', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Add a link'), 'tasklink', 'create', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Add a comment'), 'comment', 'create', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Attach a document'), 'file', 'create', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Add a screenshot'), 'file', 'screenshot', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Duplicate'), 'task', 'duplicate', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Duplicate to another project'), 'task', 'copy', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?= $this->url->link(t('Move to another project'), 'task', 'move', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <li>
            <?php if ($task['is_active'] == 1): ?>
                <?= $this->url->link(t('Close this task'), 'task', 'close', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
            <?php else: ?>
                <?= $this->url->link(t('Open this task'), 'task', 'open', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
            <?php endif ?>
        </li>
        <?php if ($this->task->canRemove($task)): ?>
        <li>
            <?= $this->url->link(t('Remove'), 'task', 'remove', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        </li>
        <?php endif ?>
    </ul>
</div>
