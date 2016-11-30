<?php
namespace CodeYellow\Api\Test\Validation\Mock;

/**
 * Validator that has a mandatory required rule.
 * Needed to verify T2562
 * @see http://phabricator.intern/T2562
 */
class RequiredValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'username' => 'required|size:5',
    ];
}
