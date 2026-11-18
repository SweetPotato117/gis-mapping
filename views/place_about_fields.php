<?php
require_once __DIR__ . '/../models/place_about.php';
$selectedAbout = decodePlaceAbout($place['place_about'] ?? null);
?>
<div class="place-about-fields">
    <?php foreach (PLACE_ABOUT_GROUPS as $aboutKey => $aboutGroup): ?>
        <fieldset class="place-about-options">
            <legend><?= $aboutGroup['title'] ?></legend>
            <div class="place-about-options-grid">
                <?php foreach ($aboutGroup['options'] as $optionKey => [$optionLabel, $optionIcon]): ?>
                    <label>
                        <input type="checkbox" name="<?= $aboutKey ?>[]" form="locationForm"
                               value="<?= $optionKey ?>" <?= in_array($optionKey, $selectedAbout[$aboutKey], true) ? 'checked' : '' ?>>
                        <svg class="place-about-option-icon" aria-hidden="true" focusable="false">
                            <use href="../assets/icons/place-details.svg?v=3#<?= $optionIcon ?>"></use>
                        </svg>
                        <span><?= $optionLabel ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    <?php endforeach; ?>
</div>
