# DpF - Degrees per Frame

The "Degrees per Frame" research aims to change how motion is displayed in videos by dynamically adjusting the frames per second for each object (via segmentation) based on the angular velocity of objects in the video. This approach ensures that fast-moving objects have more frames per second, while slow-moving objects have fewer frames per second, resulting in a more visually engaging and realistic motion experience.

To determine the optimal combination of angular velocities and frames per second for displaying motion, a PHP-based web application has been developed. This application allows users to compare and rate videos. When a user accesses the site for the first time, their IP address is used to determine the screen size. The application then presents two videos to the user and asks them to choose their preferred video. The user's choice is recorded, and the videos' rankings are updated using the Elo rating system, commonly used for ranking players in competitive games. The Elo ranking system is also used to bias the randomizer that displays the videos, as the focus is on determining the best performing videos rather than obtaining the entire distribution of "quality" for every video.

## Features

-   Prompt users to enter their monitor size for calculating angular velocity
-   Save monitor size and Elo rankings in separate text files
-   Handle form submissions for monitor size input using POST method
-   Display two videos for comparison
-   Keep track of videos' rankings using the Elo rating system
-   Record user's choice and update rankings in a CSV file
-   Handle user's choice input using GET method

## Installation

1.  Copy the code to a PHP-enabled web server.
2.  Make sure that the web server has write permissions to the directory where the code is copied, as the application writes data to files.
3.  Access the application using a web browser.

## Usage

1.  Open the DpF application in a web browser.
2.  Enter your monitor size in the format "widthxheight" if prompted.
3.  Click on the video that you prefer in the side-by-side comparison.
4.  The application will record your choice, update the rankings, and present two new videos for comparison.
5.  Repeat the process until you are done.

## Demo
A demo implementation of this PHP code can be found in https://lab.cross-compass.com/ 
This demo only shows the front-end part of the code, but the text files with the outputs of this experiment are not visible when visiting this demo. For viewing the demo you have to run a PHP-enabled web server (including a localhost, for example), to which you have read-write privileges.

## Future Work

The current approach of using pre-rendered videos in this experiment is flawed, as it does not account for variations in monitor sizes among users. Each user may have a slightly different monitor size, which can affect the angular velocity of the pre-rendered video and invalidate the results of the experiment.

The next step in this research is to generate different angular velocities in the browser based on the monitor size input provided by the user. This will allow for monitoring of the frames per second (fps) displayed on the end-user system to ensure a consistent 60fps, assuming the same seating distance. This approach will address the issue of monitor size variability and provide more accurate and reliable results in the experiment.

An example of this other approach is demonstrated in the MotionBlur.html file, where the top slider increases the number of samples of motion blur in the moving circle bellow.
