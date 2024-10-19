
                <!-- help -->
                <div class="card text-center w-100 mx-auto">

                    <p class="h4 card-header">Jilo Help</p>
                    <div class="card-body">

<div style="text-align: left; font-family: monospace; font-size: 0.75em; white-space: pre-wrap;">
<a href="https://lindeas.com/jilo">Jilo</a> is a software tools suite developed by <a href="https://lindeas.com">Lindeas Ltd.</a> designed to help in maintaining a Jitsi Meet platform.

It consists of several parts meant to run together, although some of them can be used separately.

<hr /><strong>"JILO"</strong>

This is the command-line tool for extracting information about important events from the Jitsi Meet log files, storing them in a database and searching through that database.
Jilo is written in Bash and has very minimal external dependencies. That means that you can run it on almost any Linux system with jitsi log files.

It can either:
- show the data directly in the terminal,
- provide it to an instance of the web application "Jilo Web" for displaying statistics (the Jilo Web server needs to have access to the sqlite database file from Jilo),
- or send the data output to a "Jilo Agent" that can then allow a remote Jilo Web access it.

This way Jilo is always available on each host in the Jitsi Meet platform for quick command-line search, while also providing data for the statistics on a central Jilo Web server, without any need to put additional software on each server.

<hr /><strong>"Jilo Agent"</strong>

The Jilo Agent is a small program, written in Go. It runs on remote servers in the Jitsi Meet platform, and provides info about the operation of the different components of the platform.

It listens for connections from the central Jilo Web app on a special port and the connection is secured with JWT tokens authentication. It can be configured to use HTTPS. In a firewall you only need to allow the agent's port for incoming connections from the Jilo Web server.

All information about the different services (jvb, jicofo, jigasi, nginx, prosody) is transmitted over that single port. This, plus the authentication and the fact that Jilo Agent doesn't need any additional external programs or libraries to operate, make it very easy for deploying and automating the deployment in a large Jitsi Meet setup.

<hr /><strong>"Jilo Web"</strong>

Jilo Web is the web app that combines all the information received from Jilo and Jilo Agents and shows statistics and graphs of the usage, the events and the issues.

It's a multi-user web tool with user levels and access rights integrated into it, making it suitable for the different levels in an enterprise.

The current website you are looking at is running a Jilo Web instance.

<hr /><strong>"Jilo Server"</strong>

Jilo Server is a server component written in Go, meant to work alongside Jilo Web. It is responsible for all automated tasks - health checks, periodic retrieval of data from the remote Jilo Agents, etc.

It generally works on the same machine as the web interface Jilo Web and shares its database, although if needed it could be deployed on a separate machine.

Jilo Web checks for the Jilo Server availability and displays a warning if there is a problem with the server.

</div>

                    </div>
                </div>
                <!-- /help -->
