function test(resolve,reject){
	var randomTime = Math.random()*2;
	console.log("当前获取的时间为"+randomTime);
	setTimeout(function(){
		if(randomTime<1){
			console.log("call resolve");
			resolve("这个是小于1的值");
		}
		else{
			console.log("call reject");
			reject("这个是大于1的选择");		
		}			
	},randomTime*1000);	
}

//var p1 = new Promise(test);

//var p2 = p1.then(function(result){
//	console.log("这个显示的结果为:"+result);
//});

//var p3 = p2.catch(function(reason){
//	console.log("超时间结果为:"+reason);
//})

var person1 = new Promise(function(resolve,reject){	
	setTimeout(function(){
		var work = 3;
		var sleep = 5;
		var play = 5;
		var aim = 3;
		var study = Math.random()*10;
		
		var value = work+sleep+play+study;
		resolve(value)		
	},1000)	
})



var person2 = new Promise(function(resolve,reject){
	setTimeout(function(){
		var work = 3;
		var sleep = 5;
		var play = 3;
		var aim = 20;
		var study = Math.random()*100;		
		var value = work+sleep+play+study;
		resolve(value)		
	},1000)	
})


Promise.all([person1,person2]).then(function(result){
	console.log("person1的人生:"+ result[0]);
	console.log("person2的人生:"+ result[1]);
	
})
