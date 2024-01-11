<?php defined("isInSideApplication")?null:die('no access');


$requiredFields = [
    "vendor" => [
        "username",
        "email",
        "name",
        "job_title",
        "phone_number",
        "password",
    ],
    "toc" => [
        "username",
        "email",
        "name",
        "job_title",
        "phone_number",
        "password",
    ]
];

if(empty($this->data['userData'])){
    throw new Exception('missing params `userData`');
}

if(empty($this->data['type'])){
    throw new Exception('missing params `type`');
}

$data = json_decode($this->data['userData']);
$type = $this->data['type'];

//check all fields are pressent
foreach($requiredFields[$type] ?? [] as $field){
    if(empty($data->{$field})){
        $missing[] = $field;
    }
}
if(count($missing ?? []) > 0){
    throw new Exception('missing data fields `' . implode ('`, `', $missing) .'`');
}

switch ($type){

    case 'vendor':
        $id = vendor::vSave((object)$data);
        vendor::sendActivationEmail($id, ($this->data['referrer'] ?? ''));
    break;

    case 'toc':
        $id = toc::vSave((object)$data);
    break;

    default:
    throw new Exception("Unsupported user type `{$type}`");

}

$_user = current(user::getUserDetailsById($id) ?? []) ?? [];
unset($_user['password']);
unset($_user['checksum']);

user::sendAlert($_user);


$log = new log([
    'component'    => 'user',
    'component_id' => $id ?? null,
    'details'      => 'user updated',
    'user_id'       => $id
]);
$log->save();


$this->response = 'added user ID ' . $id;