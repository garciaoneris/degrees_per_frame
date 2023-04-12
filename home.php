<?php

$monitor_file = 'monitor.txt'; // File to store monitor sizes of users
$ip = $_SERVER['REMOTE_ADDR']; // IP address of the user accessing the page
$monitor_size = ''; // Variable to store monitor size of the current user

if (file_exists($monitor_file)) { // Check if monitor file exists
    $lines = file($monitor_file); // Read lines from the monitor file
    foreach ($lines as $line) { // Loop through each line in the monitor file
        $parts = explode(',', $line); // Split the line by comma to get IP address and monitor size
        if ($parts[0] == $ip) { // Check if IP address in the file matches the current user's IP address
            $monitor_size = trim($parts[1]); // If yes, store the monitor size in $monitor_size variable
            break;
        }
    }
}

if ($monitor_size == '') { // If monitor size is not found for the current user
    // Prompt the user for their monitor size
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
    echo 'Please enter your monitor size in the format "widthxheight": ';
    echo '<input type="text" name="monitor_size">';
    echo '<input type="submit" value="Submit">';
    echo '</form>';

    // Handle the form submission
    if (isset($_POST['monitor_size'])) { // Check if form is submitted with monitor size
        $monitor_size = $_POST['monitor_size']; // Store the submitted monitor size in $monitor_size variable
        $data = $ip . ',' . $monitor_size; // Prepare data to be stored in monitor file as IP address and monitor size
        file_put_contents($monitor_file, $data . PHP_EOL, FILE_APPEND); // Append the data to monitor file
    }
}

// Populate an array with videos in the "videos" folder
$directory = 'videos/'; // Directory where videos are stored
$videos = array(); // Array to store video file names
foreach(glob($directory . '*.{mp4}', GLOB_BRACE) as $file) { // Get all files with .mp4 extension in the videos directory
    $videos[] = $file; // Add file name to the videos array
}

// Load the current ELO rankings from a file
$elo_file = 'elo.txt'; // File to store ELO rankings of videos
$elo = array(); // Array to store ELO rankings
if (file_exists($elo_file)) { // Check if ELO file exists
    $elo = unserialize(file_get_contents($elo_file)); // Read ELO rankings from the file and unserialize it into the $elo array
} else {
    // Set initial ELO rankings for each video
    $directory = 'videos/'; // Directory where videos are stored
    foreach(glob($directory . '*.{mp4}', GLOB_BRACE) as $file) { // Get all files with .mp4 extension in the videos directory
        $elo[$file] = 1000; // Set initial ELO ranking for each video as 1000
    }
}

// Calculate the total ELO of all videos
$total_elo = 0; // Variable to store total ELO ranking
foreach($elo as $video => $rank) { // Loop through each video and its ELO ranking
    $total_elo += $rank; // Add ELO ranking to the total_elo variable
}

// Assign weights to each video based on its ELO
$weights = array();
foreach($elo as $video => $rank) {
    $weight = $total_elo / $rank; // Calculate weight based on total_elo divided by rank
    $weights[$video] = $weight; // Store weight in the $weights array with video as key
}

// Select two random videos from the array, biased by their weights
$option1 = weighted_random($videos, $weights); // Get random video from $videos array using weighted_random function with $weights as weights
$option2 = weighted_random($videos, $weights); // Get another random video, making sure it's not the same as option1

// Ensure that the two options are not the same
while ($option1 == $option2) {
    $option2 = weighted_random($videos, $weights); // Keep getting random video for option2 until it's different from option1
}

// Weighted random function
function weighted_random($items, $weights) {
    $weighted_items = array();
    foreach($items as $item) {
        for ($i = 0; $i < $weights[$item]; $i++) {
            $weighted_items[] = $item; // Add the item to the array multiple times based on its weight
        }
    }
    return $weighted_items[array_rand($weighted_items)]; // Return a randomly selected item from the weighted array
}

// Record user's choice
if (isset($_GET['choice']) && isset($_GET['option1']) && isset($_GET['option2'])) {
    $user_choice = $_GET['choice']; // Get user's choice from the form
    $winner = $_GET['choice']; // Set the winner initially as the user's choice
    if($winner == $option1) { // If winner is option1, set loser as option2
        $loser = $option2;
    }
    else {
        $loser = $option1; // Otherwise, set loser as option1
    }
    $loser = $_GET['option2']; // Get loser from the form

    // Update ELO rankings based on the user's choice
    $k_factor = 32; // The K-factor determines how much a player's rating changes based on the outcome of a game.
    $expected_winner = 1 / (1 + pow(10, ($elo[$loser] - $elo[$winner]) / 400)); // Calculate expected probability of winner winning
    $expected_loser = 1 / (1 + pow(10, ($elo[$winner] - $elo[$loser]) / 400)); // Calculate expected probability of loser winning
    $actual_winner = ($user_choice == $winner) ? 1 : 0; // Set actual winner as 1 if user's choice matches winner, otherwise 0
    $actual_loser = ($user_choice == $loser) ? 1 : 0; // Set actual loser as 1 if user's choice matches loser, otherwise 0
    $new_winner_elo = $elo[$winner] + $k_factor * ($actual_winner - $expected_winner); // Update winner's ELO rating
    $new_loser_elo = $elo[$loser] + $k_factor * ($actual_loser - $expected_loser); // Update loser's ELO rating
    $elo[$winner] = $new_winner_elo; // Set new ELO rating for winner
    $elo[$loser] = $new_loser_elo; // Set new ELO rating for loser

    // Record the two options and user's choice to a file
    $data = $_GET['option1'] . ',' . $_GET['option2'] . ',' . $user_choice . ',' . $_SERVER['REMOTE_ADDR'];
    file_put_contents('record.csv', $data . PHP_EOL, FILE_APPEND);

    // Save the updated ELO rankings to a file
    file_put_contents($elo_file, serialize($elo));

}

?>


<!-- The HTML code starts here -->
<html>
<head>
    <title>DpF</title> <!-- The title of the HTML page -->
    <style>
      .center {
        position: absolute;
        top: 50%; /* Vertically center the content */
        left: 50%; /* Horizontally center the content */
        transform: translate(-50%, -50%); /* Translate the content to center it properly */
      }
    </style>
</head>
<body style="background-color:black;"> <!-- Set the background color of the body to black -->

<!-- A form that sends data to the same PHP file it is in -->
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">

    <div class="center" style="width:100%;"> <!-- A centered div that contains the videos -->
        <!-- The first video element -->
        <video ondblclick="setChoice('<?php echo $option1; ?>','<?php echo $option2; ?>','<?php echo $option1; ?>')" width="100%" height="auto" controls loop autoplay muted>
            <source src="<?php echo $option1; ?>" type="video/mp4"> <!-- The source of the video is set dynamically using PHP -->
        </video>

        <!-- The second video element -->
        <video ondblclick="setChoice('<?php echo $option1; ?>','<?php echo $option2; ?>','<?php echo $option2; ?>')" width="100%" height="auto" controls loop autoplay muted>
            <source src="<?php echo $option2; ?>" type="video/mp4"> <!-- The source of the video is set dynamically using PHP -->
        </video>
    </div>

    <!-- Hidden input fields to store the selected choice and options -->
    <input type="hidden" name="choice" id="choice">
    <input type="hidden" name="option1" id="option1">
    <input type="hidden" name="option2" id="option2">
</form>

<script>
    function setChoice(option1, option2, choice) {
        // Set the values of the hidden input fields with the selected choice and options
        document.getElementById("option1").value = option1;
        document.getElementById("option2").value = option2;
        document.getElementById("choice").value = choice;
        document.forms[0].submit(); // Submit the form
    }
    
    // Hide the controls of all video elements on the page
    var videos = document.getElementsByTagName("video");
    for (var i = 0; i < videos.length; i++) {
        videos[i].controls = false;
    }
</script>

</body>
</html>
<!-- The HTML code ends here -->