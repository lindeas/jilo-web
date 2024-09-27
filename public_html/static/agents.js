function fetchData(force = false) {
    // Show loading text
    document.getElementById("result").innerHTML = "Loading...";

    // Create an AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "index.php?page=agents", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Parse and display the result
            let result = JSON.parse(xhr.responseText);
            document.getElementById("result").innerHTML = JSON.stringify(result, null, 2);
        }
    };

    // Send the AJAX request, with force flag
    xhr.send("action=fetch&force=" + (force ? 'true' : 'false'));
}
