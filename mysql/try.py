import vlc
import time
import keyboard  # For detecting key presses

# Replace with your RTSP URL
rtsp_url = 'rtsp://admin2024:Wilfred.912816@192.168.8.108:554/stream1'

# Create an instance of VLC player
player = vlc.MediaPlayer(rtsp_url)

# Start playing the stream
player.play()

# Allow some time for the stream to start
time.sleep(5)

print("Press 'q' to stop the stream.")

# Run the player and listen for key presses
try:
    while True:
        # Check if 'q' key is pressed
        if keyboard.is_pressed('q'):
            player.stop()
            break
except KeyboardInterrupt:
    # Stop the player when interrupted
    print("Keyboard interrupt detected. Stopping stream.")
    player.stop()

# import vlc
# import time
# import keyboard
# import os

# # Replace with your RTSP URL
# rtsp_url = 'rtsp://admin2024:Wilfred.912816@192.168.8.108:554/stream1'

# # Create an instance of VLC player
# player = vlc.MediaPlayer(rtsp_url)

# # Create a screenshot folder if it doesn't exist
# screenshot_folder = 'screenshot'
# os.makedirs(screenshot_folder, exist_ok=True)

# # Set the filename for the screenshot
# screenshot_filename = 'latest_screenshot.png'
# screenshot_path = os.path.join(screenshot_folder, screenshot_filename)

# # Start playing the stream
# player.play()

# print("Taking snapshots every 3 seconds. Press 'q' to stop the stream.")

# try:
#     while True:
#         # Take a snapshot without minimizing
#         player.video_take_snapshot(0, screenshot_path, 0, 0)

#         # Wait for 3 seconds
#         time.sleep(5)

#         # Check if 'q' key is pressed to stop
#         if keyboard.is_pressed('q'):
#             player.stop()
#             break
# except KeyboardInterrupt:
#     # Stop the player when interrupted
#     print("Keyboard interrupt detected. Stopping stream.")
#     player.stop()
