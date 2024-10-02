function fetchData(agent_id, url, endpoint, jwtToken, force = false) {
    // FIXME make use of force variable

    let counter = 0;
    const resultElement = document.getElementById("result" + agent_id);
    const cacheInfoElement = document.getElementById("cacheInfo" + agent_id);

    // Show loading text
    resultElement.innerHTML = "Loading... (0 seconds)";

    // Create an interval to update the counter every second
    const intervalId = setInterval(() => {
        counter++;
        resultElement.innerHTML = `Loading... (${counter} seconds)`;
    }, 1000);

    // Create an AJAX request
    var xhr = new XMLHttpRequest();
    const agentUrl = url + endpoint;

    // DEBUG show the requested URL for debugging purpose
    //console.log("Requesting URL:", agentUrl);

    // Handle invalid URL error
    try {
        xhr.open("POST", agentUrl, true);
    } catch (e) {
        clearInterval(intervalId); // Stop the counter on error
        resultElement.innerHTML = `Error: Invalid URL ${agentUrl}<br />` + e.message;
        return; // Exit the function early
    }

    // send the token
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.setRequestHeader("Authorization", "Bearer " + jwtToken);

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
                    // DEBUG display the result
                    //console.log(xhr.responseText);

                    if (result.error) {
                        resultElement.innerHTML = "Error: " + result.error;
                    } else {
                        // show the result in the html
                        resultElement.innerHTML = JSON.stringify(result, null, 2);

                        // Show the cache timestamp from the session
                        const cacheTimestamp = new Date(result.cache_time);

                        // Display the cache retrieval date and time
                        const formattedDate = cacheTimestamp.toLocaleDateString();
                        const formattedTime = cacheTimestamp.toLocaleTimeString();
                        cacheInfoElement.innerHTML = `cache refreshed on ${formattedDate} at ${formattedTime}`;

                        // send the result to PHP to store in session
                        saveResultToSession(result, agent_id);
                    }
                } catch (e) {
                    // Display the error
                    resultElement.innerHTML = "Error: Response is not a valid JSON.<br />Response: " + xhr.responseText;
                    console.error("error:", e);
                }
            } else {
                resultElement.innerHTML = `Error: Unable to fetch data from ${agentUrl}<br />Status Code: ${xhr.status}<br />Status Text: ${xhr.statusText}<br />Response: ${xhr.responseText}`;
            }
        }
    };

    // Handle network-level errors (e.g., connection refused)
    xhr.onerror = function() {
        clearInterval(intervalId); // Stop the counter on error
        resultElement.innerHTML = `Network Error:<br />Unable to connect to ${agentUrl}<br />Check network connection or try again later.`;
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


// load the result from cache
function loadCache(agent_id) {
    const resultElement = document.getElementById("result" + agent_id);
    const cacheInfoElement = document.getElementById("cacheInfo" + agent_id);
    resultElement.innerHTML = "Loading cached data...";

    // Fetch the cached data from PHP
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "static/loadcache.php?agent="+agent_id, true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    let response = JSON.parse(xhr.responseText);

                    if (response.status === 'success') {
                        // Display the cached data
                        resultElement.innerHTML = JSON.stringify(response.data, null, 2);

                        // Get the cache timestamp from the session
                        const cacheTimestamp = new Date(response.cache_time);

                        // Display the cache retrieval date and time
                        const formattedDate = cacheTimestamp.toLocaleDateString();
                        const formattedTime = cacheTimestamp.toLocaleTimeString();
                        cacheInfoElement.innerHTML = `cache retrieved on ${formattedDate} at ${formattedTime}`;

                    } else {
                        resultElement.innerHTML = "No cached data found.";
                            cacheInfoElement.innerHTML = "";
                    }
                } catch (e) {
                    resultElement.innerHTML = "Error loading cached data.";
                    console.error("error:", e);
                }
            } else {
                resultElement.innerHTML = `Error: Unable to load cache. Status code: ${xhr.status}`;
            }
        }
    };

    xhr.onerror = function() {
        resultElement.innerHTML = "Network error occurred while fetching the cached data.";
    };

    xhr.send();
}


// we send result to PHP session, to be available to the whole app
function saveResultToSession(result, agent_id) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "?page=agents&agent="+agent_id, true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log("Data saved to session successfully.");
        }
    };

    xhr.onerror = function() {
        console.error("Error saving data to session.");
    };

    xhr.send(JSON.stringify(result));
}
