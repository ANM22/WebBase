<?php

/**
 * Redirect to ANM22 WebBase control panel.
 *
 * @copyright 2024 Paname srl
 */

include __DIR__ . "/config/license.php";

header("Location: https://www.anm22.it/app/webbase/?w=" . $anm22_wb_license);
