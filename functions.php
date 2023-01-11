<?php

declare(strict_types=1);

require 'vendor/autoload.php';


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

function bookings($name, $email, $transferCode, $arrivalDate, $departureDate, $room_id, $totalCost)
{
    $database = connect('/bookings.db');
    if (isset($_POST['name'], $_POST['email'], $_POST['transfer_code'], $_POST['arrival_date'], $_POST['departure_date'], $_POST['room_id'])) {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $transferCode = htmlspecialchars(trim($_POST['transfer_code']));
        $arrivalDate = htmlspecialchars(trim($_POST['arrival_date']));
        $departureDate = htmlspecialchars(trim($_POST['departure_date']));
        $roomId = $_POST['room_id'];
        $roomId = intval($roomId);
        $totalCost = totalCost($roomId, $arrivalDate, $departureDate);

        $query = 'INSERT INTO bookings (name, email, transfer_code, arrival_date, departure_date, room_id, total_cost) VALUES (:name, :email, :transfer_code, :arrival_date, :departure_date, :room_id, :total_cost)';

        $statement = $database->prepare($query);

        $statement->bindParam(':name', $name, PDO::PARAM_STR);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->bindParam(':transfer_code', $transferCode, PDO::PARAM_STR);
        $statement->bindParam(':arrival_date', $arrivalDate, PDO::PARAM_STR);
        $statement->bindParam(':departure_date', $departureDate, PDO::PARAM_STR);
        $statement->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $statement->bindParam(':total_cost', $totalCost, PDO::PARAM_INT);


        $receiptContent = [
            "Hotel: " . $hotel = "El Morrobocho",
            "Island: " . $island = "Isla del Cantoor",
            "Stars: " . $stars = "0",
            "Name: " . $name,
            "E-mail: " . $email,
            "Transfer-code: " . $transferCode,
            "Arrival date: " . $arrivalDate,
            "Departure date: " . $departureDate,
            "Room: " . $room_id,
            "Cost: $" . $totalCost
        ];


        $generateReceipt = file_get_contents(__DIR__ . '/confirmation.json');
        $receipt = json_decode($generateReceipt, true);
        array_push($receipt, $receiptContent);
        $json = json_encode($receipt);
        file_put_contents(__DIR__ . '/confirmation.json', $json);

        header('Content-Type: application/json');

        echo "Thank you for booking your stay at " . $hotel . ". Hope to see you soon again. \n
        Here is your receipt:\n\n" .
            json_encode(end($receipt));

        $statement->execute();
    }
};

// Calculating the total cost of every stay.

function totalCost(int $room_id, string $arrivalDate, string $departureDate)
{
    $database = connect('/bookings.db');
    $stmt = $database->prepare('SELECT price FROM rooms WHERE id = :room_id');
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();

    $roomCost = $stmt->fetch(PDO::FETCH_ASSOC);
    $roomCost = $roomCost['price'];

    $totalCost = (((strtotime($departureDate) - strtotime($arrivalDate)) / 86400) * $roomCost);
    return $totalCost;
}

/* function deposit()
{
    $client = new Client();

    $response = $client->request(
        'POST',
        'https://www.yrgopelago.se/centralbank/deposit',
        [
            'form_params' => [
                'user' => 'Filip',
                'transferCode' => "3529ace0-1217-41fb-8576-8066f191e738"
            ]
        ]
    );

    if ($response->hasHeader('Content-Length')) {
        $transfer_code = json_decode($response->getBody()->getContents());

        if (isset($transfer_code->error)) {
            $errors[] = $transfer_code->error;
            return false;
        } else {
            return true;
        }
    }
} */

/* function checkCode(string $transferCode, int $totalCost): bool
{
    $client = new Client();
    $response = $client->request(
        'POST',
        'https://www.yrgopelago.se/centralbank/transferCode',
        [
            'form_params' => [
                'transferCode' => $transferCode,
                'total_cost' => $totalCost,
            ]
        ]
    );
} */

/* function depositCode(string $transferCode, int $totalCost): bool
{
    $client = new Client();
    $response = $client->request(
        'POST',
        'https://www.yrgopelago.se/centralbank/deposit',
        [
            'form_params' => [
                'user' => "string",
                'myUsername' => "Filip",
                'total_cost' => $totalCost,
                'myTransferCode' => "3529ace0-1217-41fb-8576-8066f191e738",
                'userTransferCode' => $transferCode,
            ]
        ]
    );
} */
