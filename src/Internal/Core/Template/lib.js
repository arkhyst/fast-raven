function request(api, method, data = {}){
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

for(const req of []) {
    request(req["api"], "GET").then(data => {
        const result = data.data;
        $(req["dom"]).html(result.toString());
    })
}
