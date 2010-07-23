<?php $view->extend('LichessBundle::layout') ?>
<?php $view->stylesheets->add('bundle/lichess/css/translation.css') ?>
<?php $view->slots->set('title', 'Help translate Lichess') ?>

<div class="lichess_box">
    <h1 class="lichess_title">Help translate Lichess!</h1>
    <div class="lichess_text">
        Lichess is OpenSource and needs contributors to get better.
        <?php $message && printf('<div class="message">%s</div>', $message); ?>
        <?php $error && printf('<div class="error">%s</div>', $error); ?>
        <?php echo $form->renderFormTag($view->router->generate('lichess_translate', array('locale' => $locale)), array('data-change-url' => $view->router->generate('lichess_translate', array('locale' => '__')))) ?>
            <div class="field">
                <label for="translation_code">Translate from english to </label>
                <?php echo $form['code']->render() ?>
            </div>
            <?php if(!empty($locale)): ?>
                Please translate the following English words and phrases below.<br />
                For example, to translate from English to French:<br />
                <strong>"Level": ""</strong> becomes <strong>"Level": "Niveau"</strong><br />
                <strong>"Opponent: %ai_name%": ""</strong> becomes <strong>"Opponent: %ai_name%": "Adversaire : %ai_name%"</strong>
                <div class="field">
                    <?php echo $form['yamlMessages']->render() ?>
                </div>
                <div class="field">
                    <input type="submit" value="Submit translations" />
                </div>
            <?php endif; ?>
        </form>
        <hr />
        Current translations:
        <ul>
            <li><strong>French</strong> by Thibault Duplessis</li>
            <li><strong>Russian</strong> by Nikita Milovanov</li>
            <li><strong>Deutsch</strong> by Patrick Gawliczek</li>
            <li><strong>Türkçe</strong> by Yakup Ipek</li>
        </ul>
    </div>
</div>
