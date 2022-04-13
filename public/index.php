<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
<?php
include_once('../secrets.php');
class Server 
{
	public const base_url = "https://matrix.danastas.io";
	public const blog_space_id = "!dPlOhBJhrsDwhSnQXK:danastas.io";


	public static function get_access_token(): string
	{ 
		$params = [
			"type"		=>	"m.login.password",
			"user"		=>	"danastasio",
			"password"	=>	Secrets::password,
		];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, Server::base_url . '/_matrix/client/r0/login');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = json_decode(curl_exec($curl));
		return $response->access_token;
	}
}


function get_space_summary(string $access_token): object
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, Server::base_url . "/_matrix/client/v1/rooms/" . server::blog_space_id . "/hierarchy?access_token=" . $access_token);
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($curl));
	return $response;
}

function get_room_details(string $access_token, string $room_id): object
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, Server::base_url . "/_synapse/admin/v1/rooms/" . $room_id . "?access_token=" . $access_token);
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($curl));
	return $response;
}

function get_prev_batch(string $access_token, string $room_id): string
{
	$filter = [
		"account_data" => [
			"limit" => 0,
			"not_types" => [
				"m.*",
				"im.*"
			]
		],
		"room" => [
			"rooms" => [
				$room_id,
			]
		]
	];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, Server::base_url . "/_matrix/client/v3/sync?access_token=" . $access_token . "&filter=" . json_encode($filter));
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($curl));
	return $response->rooms->join->$room_id->timeline->prev_batch;
}

function get_post_details(string $access_token, string $room_id, string $prev_batch): string
{
	// this should only really be called once you click on the post in the webpage
	$filter = [
		"types" => [
			"m.room.message"
		]
	];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, Server::base_url . "/_matrix/client/v3/rooms/" . $room_id . "/messages?dir=f&from=" . $prev_batch ."&access_token=" . $access_token . "&filter=" . json_encode($filter));
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($curl));
	return $response->chunk[0]->content->body;
}

function list_rooms(string $access_token): void
{
	// this should be called on home page of the blog. it needs to pass info to get_post_details
	$room_list = get_space_summary($access_token);
	$rooms = [];
	foreach ($room_list->rooms as $room) {
		if ($room->room_id != Server::blog_space_id) {
			array_push($rooms, $room->room_id);
		}
	}

	echo '<div><h1 class="font-bold text-lg w-full text-center">Blog Posts:</div>' . "\n";
	echo '<div class="p-5 w-full text-center">';
	foreach ($rooms as $room) {
		$room_details = get_room_details($access_token, $room);
		if ($room_details->join_rules === "public") {
			echo '<div><h2 class="font-semibold text-md mt-2">' . $room_details->name . '</p></div>' . "\n";
			if ($room_details->topic) {
				echo '<div><p>' . $room_details->topic . '</p></div>' . "\n";
			} else {
				echo '<div><p>No topic available</p></div>';
			}
			$prev_batch = get_prev_batch($access_token, $room_details->room_id);
		} 
	}
	echo '</div>';
}

function get_post_body(): void
{
	echo "Post body: " . get_post_details(get_access_token, $room_details->room_id, $prev_batch) . "\n";
}
$access_token = Server::get_access_token();
list_rooms($access_token);
?>
