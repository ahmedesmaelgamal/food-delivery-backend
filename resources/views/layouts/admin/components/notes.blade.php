<div class="card shadow-lg mt-3">
    <div class="card-body">
        <h2 class="text-primary text-uppercase mb-3">Notes</h2>
        <div class="d-flex flex-column gap-4">
            <div class="border border-dashed border-info rounded p-3 d-flex flex-column gap-4">
                <h3 class="text-primary mb-3">Info notes</h3>
                {{-- note start --}}
                <div class="bg-info bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                    <i class="fi fi-sr-lightbulb-on text-info"></i>
                    <span>For the address setup you can simply drag the map to pick for the perfect
                        <span class="fw-semibold">Lat(Latitude) &amp;
                            Log(Longitude)
                        </span> Value.
                    </span>
                </div>
                {{-- note ends --}}
                {{-- note start --}}
                 <div class="bg-info bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                    <i class="fi fi-sr-lightbulb-on text-info"></i>
                    <span>
                       You can add multiple currency settings from here
                        <a href="#" class="text-decoration-underline fw-semibold">Currency Setup</a>.
                    </span>
                </div>
                {{-- note ends --}}
                {{-- note start --}}
                <div class="bg-info bg-opacity-10 fs-12 px-12 py-10 text-dark rounded">
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <i class="fi fi-sr-lightbulb-on text-info"></i>
                        <span>
                        In this page the read only fields data are the database &amp; installation related. They are set when you 1st install your system.
                    </span>
                    </div>
                    <ul class="m-0 ps-20 d-flex flex-column gap-1 text-body">
                        <li>The App Debug selection field is for debugging your system technical issue</li>
                        <li>When you on debug mode to check your system issues &amp; error then you can Live or Dev mode.</li>
                    </ul>
                </div>
                {{-- note ends --}}
            </div>
            <div class="border border-dashed border-warning rounded p-3 d-flex flex-column gap-4">
                <h3 class="text-primary mb-3">Warning notes</h3>
                {{-- note start --}}
                <div class="bg-warning bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                    <i class="fi fi-sr-info text-warning"></i>
                    <span>
                    Be careful when change anything in this page. They are directly connected to your system database.
                </span>
                </div>
                {{-- note ends --}}
                {{-- note start --}}
                <div class="bg-warning bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                    <i class="fi fi-sr-info text-warning"></i>
                    <span>
                        You can set default currency from here

                        <a href="#" class="text-decoration-underline fw-semibold">Currency Setup</a>.
                    </span>
                </div>
                {{-- note ends --}}
                {{-- note start --}}
                <div class="bg-warning bg-opacity-10 fs-12 px-12 py-10 text-dark rounded">
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <i class="fi fi-sr-info text-warning"></i>
                        <span>
                            In this page you can setup latest version app forcefully activate for the users. Please input proper data for the app link &amp; versions.
                        </span>
                    </div>
                    <ul class="m-0 ps-20 d-flex flex-column gap-1 text-body">
                        <li>Some time older version app can’t work properly and crash when start the app.</li>
                        <li>This section may help user to get the update features in their app.</li>
                    </ul>
                </div>
                {{-- note ends --}}
            </div>
            <div class="border border-dashed border-danger rounded p-3 d-flex flex-column gap-4">
                <h3 class="text-primary mb-3">Danger notes</h3>
                {{-- note start --}}
                <div class="bg-danger bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                    <i class="fi fi-sr-triangle-warning text-danger"></i>
                    <span>
                        Currently no payment gateway supported for <span class="fw-semibold">Euro</span> Currency. Select at least one  gateway that support euro to digital payment work properly.
                    </span>
                </div>
                {{-- note ends --}}
                {{-- note start --}}
                <div class="bg-danger bg-opacity-10 fs-12 px-12 py-10 text-dark rounded-8">
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <i class="fi fi-sr-info text-danger"></i>
                        <div>
                            <h4 class="fw-medium mb-1">V3 Version is available now. Must setup for ReCAPTCHA V3</h4>
                            <span>You must setup for V3 version. Otherwise the default reCAPTCHA will be displayed automatically</span>
                        </div>
                    </div>
                </div>
                {{-- note ends --}}
            </div>
        </div>
    </div>
</div>