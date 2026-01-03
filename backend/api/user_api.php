<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../classes/user_class.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" =>  "Database Connection Failed"]);
    exit;
}

$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            $user_data = $user->getUserById($_GET['user_id']);
            if ($user_data) {
                echo json_encode(["status" => "success", "data" => $user_data]);
            } else {
                echo json_encode(["status" => "error", "message" => "User Not Found"]);
            }
        } else {
            $users = $user->getUsers();
            echo json_encode(["status" => "success", "data" => $users]);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $action = isset($data->action) ? strtolower(trim($data->action)) : 'create user';

        if ($action === 'login') {
            if (!empty($data->email) && !empty($data->password)) {
                $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
                $password = $data->password;

                $login_result = $user->login($email, $password);
                if ($login_result) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $login_result['user_id'];
                    $_SESSION['role_id'] = $login_result['role_id'];
                    $_SESSION['full_name'] = $login_result['first_name'] . ' ' . $login_result['last_name'];

                    $role = $login_result['role_id'];
                    $redirect = '../home/dashboard.php';
                    if ($role == 1) {
                        $redirect = '../admin/dashboard.php';
                    }

                    echo json_encode(["status" => "success", "message" => "Login Successful", "redirect" => $redirect]);
                } else {
                    echo json_encode(["status" => "error", "message" => 'Invalid Email or Password']);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Email and Password Required"]);
            }
        }

        // if (
        //     !empty($data->first_name) &&
        //     !empty($data->last_name) &&
        //     !empty($data->email) &&
        //     !empty($data->password)
        // ) {
        //     $first_name = htmlspecialchars(strip_tags($data->first_name));
        //     $last_name = htmlspecialchars(strip_tags($data->last_name));
        //     $email = htmlspecialchars(strip_tags($data->email));
        //     $password = htmlspecialchars(strip_tags($data->password));

        //     if ($user->createUser($first_name, $last_name, $email, $password)) {
        //         echo json_encode(["status" => "success", "message" => "User Created Successfully"]);
        //     } else {
        //         echo json_encode(["status" => "error", "message" => "User Creation Failed"]);
        //     }
        // } else {
        //     echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
        // }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        // if (
        //     !empty($data->user_id) &&
        //     !empty($data->first_name) &&
        //     !empty($data->last_name) &&
        //     !empty($data->email)
        // ) {
        //     $user_id = htmlspecialchars(strip_tags($data->user_id));
        //     $first_name = htmlspecialchars(strip_tags($data->first_name));
        //     $last_name = htmlspecialchars(strip_tags($data->last_name));
        //     $email = htmlspecialchars(strip_tags($data->email));

        //     if ($user->editUser($user_id, $first_name, $last_name, $email)) {
        //         echo json_encode(["status" => "success", "message" => "User Updated Successfully"]);
        //     } else {
        //         echo json_encode(["status" => "error", "message" => "User Update Failed"]);
        //     }
        // } else {
        //     echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
        // }
        break;
    case 'DELETE':
        if (isset($_GET['user_id'])) {
            $result = $user->deleteUser($_GET['user_id']);

            if ($result) {
                echo json_encode(["status" => "success", "message" => "User Deleted Successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "User Deletion Failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "User ID Not Provided"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
