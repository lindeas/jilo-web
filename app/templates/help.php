<!-- help -->
<div class="container-fluid mt-2">
    <div class="row mb-4">
        <div class="col-12 mb-4">
            <h2>Help</h2>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <!-- Introduction -->
            <div class="mb-5">
                <p class="lead">
                    <a href="https://lindeas.com/jilo" class="text-decoration-none">Jilo</a> is a software tools suite developed by 
                    <a href="https://lindeas.com" class="text-decoration-none">Lindeas Ltd.</a> designed to help in maintaining a Jitsi Meet platform.
                </p>
                <p>It consists of several parts meant to run together, although some of them can be used separately.</p>
            </div>

            <!-- Components -->
            <div class="row g-4">
                <!-- Jilo CLI -->
                <div class="col-12">
                    <div class="card border bg-light">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-terminal me-2 text-secondary"></i>
                            <h5 class="card-title mb-0">JILO</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                This is the command-line tool for extracting information about important events from the Jitsi Meet log files, 
                                storing them in a database and searching through that database.
                            </p>
                            <p class="card-text">
                                Jilo is written in Bash and has very minimal external dependencies. That means that you can run it on almost 
                                any Linux system with jitsi log files.
                            </p>
                            <div class="mt-3">
                                <p class="mb-2">It can either:</p>
                                <ul class="list-unstyled ps-4">
                                    <li><i class="fas fa-check text-success me-2"></i>Show the data directly in the terminal</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Provide it to an instance of "Jilo Web" for displaying statistics</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Send the data output to a "Jilo Agent"</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jilo Agent -->
                <div class="col-12">
                    <div class="card border bg-light">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-robot me-2 text-secondary"></i>
                            <h5 class="card-title mb-0">Jilo Agent</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                The Jilo Agent is a small program, written in Go. It runs on remote servers in the Jitsi Meet platform, 
                                and provides info about the operation of the different components of the platform.
                            </p>
                            <div class="mt-3">
                                <h6 class="fw-bold mb-2">Key Features:</h6>
                                <ul class="list-unstyled ps-4">
                                    <li><i class="fas fa-shield-alt text-primary me-2"></i>Secured with JWT tokens authentication</li>
                                    <li><i class="fas fa-lock text-primary me-2"></i>HTTPS support</li>
                                    <li><i class="fas fa-network-wired text-primary me-2"></i>Single port for all services</li>
                                    <li><i class="fas fa-box text-primary me-2"></i>No external dependencies</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jilo Web -->
                <div class="col-12">
                    <div class="card border bg-light">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-globe me-2 text-secondary"></i>
                            <h5 class="card-title mb-0">Jilo Web</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Jilo Web is the web app that combines all the information received from Jilo and Jilo Agents and shows 
                                statistics and graphs of the usage, the events and the issues.
                            </p>
                            <p class="card-text">
                                It's a multi-user web tool with user levels and access rights integrated into it, making it suitable 
                                for the different levels in an enterprise.
                            </p>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                The current website you are looking at is running a Jilo Web instance.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jilo Server -->
                <div class="col-12">
                    <div class="card border bg-light">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-server me-2 text-secondary"></i>
                            <h5 class="card-title mb-0">Jilo Server</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Jilo Server is a server component written in Go, meant to work alongside Jilo Web. It is responsible for 
                                all automated tasks - health checks, periodic retrieval of data from the remote Jilo Agents, etc.
                            </p>
                            <p class="card-text">
                                It generally works on the same machine as the web interface Jilo Web and shares its database, although 
                                if needed it could be deployed on a separate machine.
                            </p>
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Jilo Web checks for the Jilo Server availability and displays a warning if there is a problem with the server.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /help -->
