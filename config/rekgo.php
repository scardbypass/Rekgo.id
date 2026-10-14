<?php
return [
    // Do not enable before legal holds and disputes are implemented and tested.
    'proof_cleanup_enabled' => (bool) env('REKGO_PROOF_CLEANUP_ENABLED', false),
 'gopay_live_verified'=>env('GOPAY_LIVE_VERIFIED',false)];
