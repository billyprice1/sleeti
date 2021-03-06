<?php

/**
 * This file is part of sleeti.
 * Copyright (C) 2016  Eliot Partridge
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Sleeti\Validation\Rules;

use Sleeti\Models\UserTfaRecoveryToken;
use Respect\Validation\Rules\AbstractRule;

class TwoFactorAuthCode extends AbstractRule
{
	protected $tfa;

	protected $user;

	public function __construct($tfa, $user) {
		$this->tfa  = $tfa;
		$this->user = $user;
	}

	public function validate($input) {
		$recoveryToken = UserTfaRecoveryToken::where('user_id', $this->user->id)->where('token', hash('sha384', $input))->first();

		if ($recoveryToken) {
			$recoveryToken->delete();
			return true;
		}

		return $this->tfa->verifyCode($this->user->settings->tfa_secret, $input);
	}
}
