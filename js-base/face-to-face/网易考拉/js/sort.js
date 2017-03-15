
// 快速排序 复杂度 O(nlogn)
// 第一种 用空间去换时间
function quickSort( arr ) {
	var leftArr = []
	,   rightArr = []

	if(arr.length <= 1) return arr;

	for (var i = 1; i < arr.length; i++) {
		if( arr[0] <= arr[i] ){
			rightArr.push(arr[i])
		} else {
			leftArr.push(arr[i])
		}
	}
	return Array.prototype.concat( quickSort(leftArr), arr[0], quickSort(rightArr) )
}

// 第二种 
function quickSort( arr ) {

	function sort(start,end) {
		var i = start,
			j = end - 1,
			index = arr[start];
		if (i >= j) {
			return;
		}
		while( i < j ){
			for (; i < j; j--) {
				if(index > arr[j]){
					arr[i++] = arr[j]
					break;
				}
			}
			for (; i < j; i++) {
				if(index <= arr[i]){
					arr[j--] = arr[i]
					break;
				}
			}
		}
		arr[j] = index;
		sort(start,j)
		sort(j+1,end)
	}

	sort(0, arr.length)
	return arr
}

// 冒泡排序
function bubbleSort( arr ) {
	var len = arr.length
	for (var i = len; i > 0; i--) {
		for (var j = 0; j < i; j++) {
			if( arr[j] < arr[j+1] ){
				var t = arr[j]
				arr[j] = arr[j+1]
				arr[j+1] = t
			}
		}
	}
}

var arr = [5,3,6,7,2,4,8,6,3,7,4,1,5,89,63,5]
console.time('11111')
arr = bubbleSort(arr)
console.timeEnd('11111')
// arr.sort()
console.log(arr)