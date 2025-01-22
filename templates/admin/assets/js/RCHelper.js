var RC_Helper = {
    
    parseToAssoc: function(array)
    {
        var result = {};
        $.each(array, function(index, data){
            var name = data.name;
            var value = data.value;
            result[name] = value;
        });
        
        return result;
    }
};


