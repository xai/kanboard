<div class="page-header">
    <h2><?= t('Day timetable') ?></h2>
</div>

<?php if (! empty($timetable)): ?>

<table class="table-fixed table-stripped">
    <tr>
        <th><?= t('Start time') ?></th>
        <th><?= t('End time') ?></th>
        <th><?= t('Action') ?></th>
    </tr>
    <?php foreach ($timetable as $slot): ?>
    <tr>
        <td><?= $slot['start'] ?></td>
        <td><?= $slot['end'] ?></td>
        <td>
            <?= $this->url->link(t('Remove'), 'timetableday', 'confirm', array('user_id' => $user['id'], 'slot_id' => $slot['id'])) ?>
        </td>
    </tr>
    <?php endforeach ?>
</table>

<h3><?= t('Add new time slot') ?></h3>
<?php endif ?>

<form method="post" action="<?= $this->url->href('timetableday', 'save', array('user_id' => $user['id'])) ?>" autocomplete="off">

    <?= $this->form->hidden('user_id', $values) ?>
    <?= $this->form->csrf() ?>

    <?= $this->form->label(t('Start time'), 'start') ?>
    <?= $this->form->select('start', $this->dt->getDayHours(), $values, $errors) ?>

    <?= $this->form->label(t('End time'), 'end') ?>
    <?= $this->form->select('end', $this->dt->getDayHours(), $values, $errors) ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</form>

<p class="alert alert-info">
    <?= t('This timetable is used when the checkbox "all day" is checked for scheduled time off and overtime.') ?>
</p>