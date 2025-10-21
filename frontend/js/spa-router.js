//  SPA Router, Handles client-side routing without page reloads


const SPARouter = {
    routes: {},
    currentRoute: null,
    contentContainer: '#app-content',

    // Initialize the router
     
    init: function() {
      
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.route) {
                this.loadRoute(e.state.route, false);
            }
        });

      
        window.addEventListener('hashchange', () => {
            const route = window.location.hash.slice(1) || 'home';
            this.loadRoute(route, true);
        });

       
        document.addEventListener('click', (e) => {
            const target = e.target.closest('a[href^="#"]');
            if (target) {
                e.preventDefault();
                const route = target.getAttribute('href').slice(1);
                this.navigate(route);
            }
        });

      
        const initialRoute = window.location.hash.slice(1) || 'home';
        this.loadRoute(initialRoute, true);
    },

    
     
    register: function(path, viewPath, callback) {
        this.routes[path] = {
            viewPath: viewPath,
            callback: callback
        };
    },

    navigate: function(route, params = {}) {
        window.location.hash = route;
        this.loadRoute(route, true, params);
    },

    loadRoute: function(route, pushState = true, params = {}) {
        let routeConfig = this.routes[route];
        let routeParams = params;

        if (!routeConfig) {
            for (let registeredRoute in this.routes) {
                if (registeredRoute.includes(':')) {
                    const pattern = new RegExp('^' + registeredRoute.replace(/:[^\s/]+/g, '([^/]+)') + '$');
                    const match = route.match(pattern);
                    if (match) {
                        routeConfig = this.routes[registeredRoute];
                        const paramNames = registeredRoute.match(/:[^\s/]+/g).map(p => p.slice(1));
                        routeParams = {};
                        paramNames.forEach((name, index) => {
                            routeParams[name] = match[index + 1];
                        });
                        break;
                    }
                }
            }
        }

        if (!routeConfig) {
            console.error(`Route not found: ${route}`);
            this.navigate('404');
            return;
        }

        this.currentRoute = route;

        if (pushState) {
            history.pushState({ route: route }, '', `#${route}`);
        }

        this.loadView(routeConfig.viewPath, routeConfig.callback, routeParams);
    },


    loadView: function(viewPath, callback, params = {}) {
        const container = $(this.contentContainer);

        container.html('<div class="text-center mt-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        fetch(`views/${viewPath}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('View not found');
                }
                return response.text();
            })
            .then(html => {
                container.html(html);
                if (callback && typeof callback === 'function') {
                    callback(params);
                }
            })
            .catch(error => {
                console.error('Error loading view:', error);
                container.html(`
                    <div class="container mt-5">
                        <div class="alert alert-danger">
                            <h4>Error Loading Page</h4>
                            <p>The requested page could not be loaded.</p>
                        </div>
                    </div>
                `);
            });
    },


    getCurrentRoute: function() {
        return this.currentRoute;
    }
};

window.SPARouter = SPARouter;
