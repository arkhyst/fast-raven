class Lib {
    /**
     * Send a request to an API endpoint.
     * @param {string} api - URL of the API endpoint.
     * @param {string} method - HTTP method to use (e.g. GET, POST, PUT, DELETE).
     * @param {Object} [data] - Optional data to send with the request.
     * @returns {Promise} A promise that resolves with the response from the API endpoint.
     */
    static request(api, method, data = undefined) {
        return new Promise(function(resolve, reject){
            $.ajax({
                url: api,
                method: method,
                data: JSON.stringify(data) ?? data,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN ?? ''
                },
                success: function(response){
                    resolve(response);
                },
                error: function(xhr, status, error){
                    reject(error);
                }
            });
        });
    }
}

for(const req of XXX_PHP_AUTOFILL) {
    Lib.request(req["api"], "GET").then(data => {
        const result = data.data;
        if(result == null || result.length == 0) return;
        
        $(req["dom"]).html(result.toString());
    });
} 
