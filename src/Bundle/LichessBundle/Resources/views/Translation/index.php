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
                    <label for="translation_author">Author (optional)</label>
                    <?php echo $form['author']->render() ?>
                </div>
                <div class="field">
                    <label for="translation_comment">Comment (optional)</label>
                    <?php echo $form['comment']->render() ?>
                </div>
                <div class="field">
                    <input type="submit" value="Submit translations" />
                </div>
            <?php endif; ?>
        </form>
        <hr />
        Big thanks go to all translators!
        <ul>
            <li><strong>French</strong> Thibault Duplessis</li>
            <li><strong>Russian</strong> Nikita Milovanov</li>
            <li><strong>Deutsch</strong> Patrick Gawliczek</li>
            <li><strong>Turkish</strong> Yakup Ipek</li>
            <li><strong>Serbian</strong> Nenad Nikolić</li>
            <li><strong>Latvian</strong> [?]</li>
            <li><strong>Bosnian</strong> Jacikka</li>
            <li><strong>Dansk</strong> Henrik Bjornskov</li>
            <li><strong>Español</strong> FennecFoxz</li>
            <li><strong>român</strong> Cristian Niţă</li>
            <li><strong>Italian</strong> Marco Barberis</li>
            <li><strong>Finnish</strong> Juuso Vallius</li>
        </ul>
    </div>
</div>
