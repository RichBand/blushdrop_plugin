/**
 * Created by ricardobandala on 2016-08-15.
 */














var me = new Human(genetics, environment);
 me = life(me);
function life(me){
    while(me.age != me.deatDate){
        if(me.awesomeLevel <= 9000 ){
            me = new Average(me);
        }
        else {
            me = new SoftwareDeveloper(me);
        }
        me.min = me.age++;
        me.deatDate = getDeathDate(me);
    }
    if (me.religion != null){
        me = null;
    }
    else{
        me = null;
    }
}
function getDeathDate(me){
    me.location = geolocation.getCurrentPosition();
    var max = LifeExpectancy(me);
    var min = me.min;
    var exp = Math.floor(Math.random() * (max - min + 1) + min);
    return exp;
}



