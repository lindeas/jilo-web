function fetchData(agent_id, url, force = false) {

    let counter = 0;
    const resultElement = document.getElementById("result" + agent_id);

    // Show loading text
    resultElement.innerHTML = "Loading... (0 seconds)";

    // Create an interval to update the counter every second
    const intervalId = setInterval(() => {
        counter++;
        resultElement.innerHTML = `Loading... (${counter} seconds)`;
    }, 1000);

    // Create an AJAX request
    var xhr = new XMLHttpRequest();

    // Handle invalid URL error
    try {
        xhr.open("POST", url, true);
    } catch (e) {
        clearInterval(intervalId); // Stop the counter on error
        resultElement.innerHTML = `Error: Invalid URL ${url}<br />` + e.message;
        return; // Exit the function early
    }

    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Set a timeout in milliseconds (10 seconds = 10000 ms)
    xhr.timeout = 10000;

    // Handle the request state change
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            clearInterval(intervalId); // Stop the counter when the request completes
            clearTimeout(requestTimeout); // Clear the timeout if response is received

            if (xhr.status === 200) {
                try {
                    // Parse and display the result
                    let result = JSON.parse(xhr.responseText);
                    resultElement.innerHTML = JSON.stringify(result, null, 2);
                } catch (e) {
                    // Display the error
                    resultElement.innerHTML = "Error: Response is not a valid JSON.<br />Response: " + xhr.responseText;
                }
            } else {
                resultElement.innerHTML = `Error: Unable to fetch data from ${url}<br />Status Code: ${xhr.status}<br />Status Text: ${xhr.statusText}<br />Response: ${xhr.responseText}`;
            }
        }
    };

    // Handle network-level errors (e.g., connection refused)
    xhr.onerror = function() {
        clearInterval(intervalId); // Stop the counter on error
        resultElement.innerHTML = `Network Error:<br />Unable to connect to ${url}<br />Check network connection or try again later.`;
    };

    // Handle the timeout event
    xhr.ontimeout = function() {
        clearInterval(intervalId); // Stop the counter on timeout
        resultElement.innerHTML = "Request timed out. Please try again.";
    };

    // Additional manual timeout
    var requestTimeout = setTimeout(function() {
        if (xhr.readyState !== 4) {
            xhr.abort(); // Abort the request if still pending after timeout
            clearInterval(intervalId); // Stop the counter
            resultElement.innerHTML = "Request timed out.";
        }
    }, 10000); // 10 seconds

    // Send the AJAX request, with force flag
    xhr.send("action=fetch&agent_id=" + agent_id + "&force=" + (force ? 'true' : 'false'));

//    // If the request finishes quickly, set this up to show at least 1 second delay
//    setTimeout(function() {
//        if (counter === 0) {
//            counter++;
//            resultElement.innerHTML = `Loading... (${counter} seconds)`;
//        }
//    }, 1000); // Simulate a minimum 1 second delay for testing

}
