<?php echo $form->renderErrors() ?>
<?php echo $form->renderHiddenFields() ?>
<div class="field message">
    <?php echo $form['subject']->renderErrors() ?>
    <label for="<?php echo $form['subject']->getId() ?>">Subject</label>
    <?php echo $form['subject']->render() ?>
</div>
<div class="field message">
    <?php echo $form['category']->renderErrors() ?>
    <label for="<?php echo $form['category']->getId() ?>">Category</label>
    <?php echo $form['category']->render() ?>
</div>
<div class="field message">
    <?php echo $form['message']->renderErrors() ?>
    <label for="<?php echo $form['message']->getId() ?>">Message</label>
    <?php echo $form['message']->render() ?>
</div>