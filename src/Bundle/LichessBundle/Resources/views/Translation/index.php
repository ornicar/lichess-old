<?php $view->extend('LichessBundle::layout.php') ?>
<?php $view['stylesheets']->add('bundles/lichess/css/translation.css') ?>
<?php $view['slots']->set('title', 'Help translate Lichess') ?>

<div class="lichess_box">
    <h1 class="lichess_title">Help translate Lichess!</h1>
    <div class="lichess_text">
        Lichess is OpenSource and needs contributors to get better.
        <?php $message && printf('<div class="message">%s</div>', $message); ?>
        <?php $error && printf('<div class="error">%s</div>', $error); ?>
        <form action="<?php echo $view['router']->generate('lichess_translate', array('locale' => $locale)) ?>" data-change-url="<?php echo $view['router']->generate('lichess_translate', array('locale' => '__')) ?>" method="post">
            <div class="field">
                <label for="translation_code">Translate from english to </label>
                <?php echo $form['code']->widget() ?>
            </div>
            <?php if(!empty($locale)): ?>
                Please translate the following English words and phrases below.<br />
                For example, to translate from English to French:<br />
                <strong>"Level": ""</strong> becomes <strong>"Level": "Niveau"</strong><br />
                <strong>"Opponent: %ai_name%": ""</strong> becomes <strong>"Opponent: %ai_name%": "Adversaire : %ai_name%"</strong>
                <div class="field">
                    <?php echo $form['yamlMessages']->widget(array(), 'LichessBundle:Translation:textarea_field.php') ?>
                </div>
                <div class="field">
                    <label for="translation_author">Author (optional)</label>
                    <?php echo $form['author']->widget() ?>
                </div>
                <div class="field">
                    <label for="translation_comment">Comment (optional)</label>
                    <?php echo $form['comment']->widget() ?>
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
            <li><strong>Danish</strong> Henrik Bjornskov</li>
            <li><strong>Spanish</strong> FennecFoxz</li>
            <li><strong>Romanian</strong> Cristian Niţă</li>
            <li><strong>Italian</strong> Marco Barberis</li>
            <li><strong>Finnish</strong> Juuso Vallius</li>
            <li><strong>Ukrainian</strong> alterionisto</li>
            <li><strong>Portuguese</strong> Arthur Braz</li>
            <li><strong>polski</strong> M3n747</li>
            <li><strong>Dutch</strong> Kintaro</li>
            <li><strong>Vietnamese</strong> Xiblack</li>
            <li><strong>Swedish</strong> nizleib</li>
            <li><strong>Czech</strong> Martin</li>
            <li><strong>Slovak</strong> taiga</li>
            <li><strong>Magyar</strong> LTBakemono</li>
            <li><strong>Catalan</strong> AI8</li>
        </ul>
    </div>
</div>
