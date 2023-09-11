<?php

namespace App\Http\Controllers\Shikimori;

use App\Models\Shikimori\UserModel;

class ShikimoriUserApiController {
    /**
     * User info
     *
     * @param UserModel $userModel
     *
     * @return array
     */
    public function me( UserModel $userModel ): array {
        return $userModel->me();
    }
}
