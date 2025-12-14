/*!
 * Spapp - Simple Single Page Application Router
 * Adapted for AlbumRate project
 */
(function($) {
    $.spapp = function(options) {
        var routes = {};
        var settings = $.extend({
            defaultView: $("main#spapp > section:first").attr("id") || "home",
            templateDir: "./views/",
            pageNotFound: false
        }, options);

        // Initialize all sections from the DOM
        $("main#spapp > section").each(function(index, element) {
            var section = $(element);
            var id = section.attr("id");
            routes[id] = {
                view: id,
                load: section.data("load") || null,
                onCreate: function() {},
                onReady: function() {}
            };
        });

        // Add a route
        this.route = function(config) {
            $.extend(routes[config.view], config);
        };

        // Load a view
        var loadView = function() {
            var hash = window.location.hash.slice(1);
            var view = hash || settings.defaultView;
            
            // Remove any quotes or invalid characters
            view = view.replace(/^['"]|['"]$/g, '').trim();
            
            // Handle query parameters (e.g., search?genre=rock)
            var baseView = view.split('?')[0].split('/')[0];
            var route = routes[baseView] || routes[view];
            
            if (!baseView || !route) {
                console.error("Invalid route:", view);
                window.location.hash = settings.defaultView;
                return;
            }
            
            var section = $("#" + baseView);
            
            // If base view doesn't exist, try full view
            if (!section.length) {
                var cleanView = view.split('?')[0].split('/')[0];
                section = $("#" + cleanView);
            }

            if (!section.length || !route) {
                console.error("View not found:", view);
                if (settings.pageNotFound) {
                    window.location.hash = settings.pageNotFound;
                } else {
                    window.location.hash = settings.defaultView;
                }
                return;
            }

            // Hide all sections
            $("main#spapp > section").hide();
            
            // Show current section
            section.show();

            if (section.hasClass("spapp-created")) {
                // Already created, just call onReady
                route.onReady();
            } else {
                // First time loading
                section.addClass("spapp-created");
                if (route.load) {
                    // Load external HTML
                    section.load(settings.templateDir + route.load, function(response, status) {
                        if (status === "success") {
                            route.onCreate();
                            route.onReady();
                        } else {
                            console.error("Failed to load:", route.load);
                        }
                    });
                } else {
                    // No external file to load
                    route.onCreate();
                    route.onReady();
                }
            }
        };

        // Run the router
        this.run = function() {
            // Listen for hash changes
            window.addEventListener("hashchange", function() {
                loadView();
            });

            // Load initial view
            if (window.location.hash) {
                loadView();
            } else {
                window.location.hash = settings.defaultView;
            }
        };

        return this;
    };
})(jQuery);
