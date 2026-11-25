<?php

return [
    // Explicitly opt a hosted classroom demo into shared evaluation credentials.
    'evaluation_enabled' => (bool) env('POS_EVALUATION_MODE', false),
];
