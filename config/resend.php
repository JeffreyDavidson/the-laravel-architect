<?php

/*
|--------------------------------------------------------------------------
| Resend
|--------------------------------------------------------------------------
|
| Merged over the package defaults. The package's own webhook route stays
| unregistered: Resend events arrive only at the signed and rate-limited
| `webhooks.resend` route.
|
*/

return [

    'routes' => false,

];
