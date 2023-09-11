<?php

namespace App\Http\Controllers\MyAnimeList;

use App\Models\MyAnimeList\UserModel;

class MalUserApiController {
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
