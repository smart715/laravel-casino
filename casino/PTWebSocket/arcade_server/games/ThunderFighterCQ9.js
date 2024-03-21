
function Game(emitter,sys,utils) {

var _self = this;   





///////////////////////////////////////
///////////////////////////////////////


with(BinaryParser = function(e, t) {
    this.bigEndian = e, this.allowExceptions = t;
}, {
    p: BinaryParser.prototype
}) {
    with(p.encodeFloat = function(e, t, i) {
        var n, o, s, a, c, l = [],
            h = Math.pow(2, i - 1) - 1,
            u = 1 - h,
            _ = h,f
            g = u - t,
            d = isNaN(b = parseFloat(e)) || b == -(1 / 0) || b == +(1 / 0) ? b : 0,
            f = 0,
            m = 2 * h + 1 + t + 3,
            p = new Array(m),
            y = (b = 0 !== d ? 0 : b) < 0,
            b = Math.abs(b),
            F = Math.floor(b),
            k = b - F;
        for (n = m; n; p[--n] = 0);
        for (n = h + 2; F && n; p[--n] = F % 2, F = Math.floor(F / 2));
        for (n = h + 1; k > 0 && n;
            (p[++n] = ((k *= 2) >= 1) - 0) && --k);
        for (n = -1; ++n < m && !p[n];);
        if (p[(o = t - 1 + (n = (f = h + 1 - n) >= u && f <= _ ? n + 1 : h + 1 - (f = u - 1))) + 1]) {
            if (!(s = p[o]))
                for (a = o + 2; !s && a < m; s = p[a++]);
            for (a = o + 1; s && --a >= 0;
                (p[a] = !p[a] - 0) && (s = 0));
        }
        for (n = n - 2 < 0 ? -1 : n - 3; ++n < m && !p[n];);
        for ((f = h + 1 - n) >= u && f <= _ ? ++n : f < u && (f != h + 1 - m && f < g && this.warn("encodeFloat::float underflow"), n = h + 1 - (f = u - 1)), (F || 0 !== d) && (this.warn(F ? "encodeFloat::float overflow" : "encodeFloat::" + d), f = _ + 1, n = h + 2, d == -(1 / 0) ? y = 1 : isNaN(d) && (p[n] = 1)), b = Math.abs(f + h), a = i + 1, c = ""; --a; c = b % 2 + c, b = b >>= 1);
        for (b = 0, a = 0, n = (c = (y ? "1" : "0") + c + p.slice(n, n + t).join("")).length, r = []; n; b += (1 << a) * c.charAt(--n), 7 == a && (r[r.length] = String.fromCharCode(b), l[r.length - 1] = b, b = 0), a = (a + 1) % 8);
        return r[r.length] = b ? String.fromCharCode(b) : "", this.bigEndian ? l.reverse() : l
    }, p.encodeInt = function(e, t, i) {
        var n = [],
            o = t / 8,
            s = Math.pow(2, t),
            r = [];
        for ((e >= s || e < -(s / 2)) && this.warn("encodeInt::overflow") && (e = 0), e < 0 && (e += s); e; r[r.length] = String.fromCharCode(e % 256), n[r.length - 1] = e % 256, e = Math.floor(e / 256));
        for (t = -(-t >> 3) - r.length; t--; r[r.length] = "\0");
        for (var a = n.length; a < o; a++) n.push(0);
        return this.bigEndian ? n.reverse() : n
    }, p.decodeFloat = function(e, t, i) {
        var n, o, s, r = ((r = new this.Buffer(this.bigEndian, e)).checkBuffer(t + i + 1), r),
            a = Math.pow(2, i - 1) - 1,
            c = r.readBits(t + i, 1),
            l = r.readBits(t, i),
            h = 0,
            u = 2,
            _ = r.buffer.length + (-t >> 3) - 1;
        do
            for (n = r.buffer[++_], o = t % 8 || 8, s = 1 << o; s >>= 1; n & s && (h += 1 / u), u *= 2); while (t -= o) return l == 1 + (a << 1) ? h ? NaN : c ? -(1 / 0) : +(1 / 0) : (1 + c * -2) * (l || h ? l ? Math.pow(2, l - a) * (1 + h) : Math.pow(2, 1 - a) * h : 0)
    }, p.decodeInt = function(e, t, i) {
        var n = new this.Buffer(this.bigEndian, e),
            o = n.readBits(0, t),
            s = Math.pow(2, t);
        return i && o >= s / 2 ? o - s : o
    }, {
        p: (p.Buffer = function(e, t) {
            this.bigEndian = e || 0, this.buffer = [], this.setBuffer(t)
        }).prototype
    }) p.readBits = function(e, t) {
        if (e < 0 || t <= 0) return 0;
        this.checkBuffer(e + t);
        for (var i, n = e % 8, o = this.buffer.length - (e >> 3) - 1, s = this.buffer.length + (-(e + t) >> 3), r = o - s, a = (this.buffer[o] >> n & (1 << (r ? 8 - n : t)) - 1) + (r && (i = (e + t) % 8) ? (this.buffer[s++] & (1 << i) - 1) << (r-- << 3) - n : 0); r; a += function(e, t) {
                for (++t; --t; e = 1073741824 == (1073741824 & (e %= 2147483648)) ? 2 * e : 2 * (e - 1073741824) + 2147483647 + 1);
                return e
            }(this.buffer[s++], (r-- << 3) - n));
        return a
    }, p.setBuffer = function(e) {
        if (e)
            for (var t, i = t = e.length, n = this.buffer = new Array(t); i; n[t - i] = e[--i]);
    }, p.hasNeededBits = function(e) {
        return this.buffer.length >= -(-e >> 3)
    }, p.checkBuffer = function(e) {
        if (!this.hasNeededBits(e)) throw new Error("checkBuffer::missing bytes")
    };
    p.warn = function(e) {
        if (this.allowExceptions) throw new Error(e);
        return 1
    }, p.fromSmall = function(e) {
        return this.encodeInt(e, 8, !0)
    }, p.fromByte = function(e) {
        return this.encodeInt(e, 8, !1)
    }, p.fromWord = function(e) {
        return this.encodeInt(e, 16, !0)
    }, p.fromShort = function(e) {
        return this.encodeInt(e, 16, !1)
    }, p.fromInt = function(e) {
        return this.encodeInt(e, 32, !0)
    }, p.fromDWord = function(e) {
        return this.encodeInt(e, 32, !1)
    }, p.fromLong = function(e) {
        return this.encodeInt(e, 64, !0)
    }, p.fromFloat = function(e) {
        return this.encodeFloat(e, 23, 8)
    }, p.fromDouble = function(e) {
        return this.encodeFloat(e, 52, 11)
    }, p.toSmall = function(e) {
        return this.decodeInt(e, 8, !0)
    }, p.toByte = function(e) {
        return this.decodeInt(e, 8, !1)
    }, p.toWord = function(e) {
        return this.decodeInt(e, 16, !0)
    }, p.toShort = function(e) {
        return this.decodeInt(e, 16, !1)
    }, p.toInt = function(e) {
        return this.decodeInt(e, 32, !0)
    }, p.toDWord = function(e) {
        return this.decodeInt(e, 32, !1)
    }, p.toLong = function(e) {
        return this.decodeInt(e, 64, !0)
    }, p.toFloat = function(e) {
        return this.decodeFloat(e, 23, 8)
    }, p.toDouble = function(e) {
        return this.decodeFloat(e, 52, 11)
    }
}
MemoryStream = function() {
    this._position = 0, this._buffer = [], this.concatenate = function(e) {
        var t = new Uint8Array(this._buffer.length + e.length);
        t.set(this._buffer, 0), t.set(e, this._buffer.length), this._buffer = t, this._position = this._buffer.length - 1
    }.bind(this), this.initialBuffer = function(e) {
        this._buffer = e, this._position = 0
    }.bind(this), this.getData = function() {
        return this._buffer
    }.bind(this), this.getPosition = function() {
        return this._position
    }.bind(this), this.setPosition = function(e) {
        this._position = e
    }.bind(this), this.getLength = function() {
        return this._buffer.length
    }.bind(this)
}, Uint8Array.prototype.slice = function(e, t) {
    var i = Array.from(this);
    return i = i.slice(e, t)
}, ProtocolBuilder = function() {
    return this._parser = new BinaryParser(!1, !0), this._decode_get_buffer = function(e, t) {
        if (e.getPosition() + t > e.getLength()) throw Error("Invalid Length");
        return e.getData().slice(e.getPosition(), e.getPosition() + t)
    }.bind(this), this.Encode_FromBool = function(e, t) {
        var i = this._parser.fromByte(t ? 1 : 0);
        e.concatenate(i)
    }.bind(this), this.Encode_FromEnum = function(e, t) {
        this.Encode_FromString(e, "u1");
        var i = this._parser.fromByte(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromByte = function(e, t) {
        var i = this._parser.fromByte(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromShort = function(e, t) {
        var i = this._parser.fromWord(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromUShort = function(e, t) {
        var i = this._parser.fromShort(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromInt = function(e, t) {
        var i = this._parser.fromInt(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromInt64 = function(e, t) {
        var i = this._parser.fromLong(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromDouble = function(e, t) {
        var i = this._parser.fromDouble(t);
        e.concatenate(i)
    }.bind(this), this.Encode_FromString = function(e, t) {
		////console.log('Encode_FromString',e, t);
        for (var i = unescape(encodeURIComponent(t)), n = [], o = 0; o < i.length; o++) n.push(i.charCodeAt(o));
        this.Encode_FromInt(e, n.length), e.concatenate(n)
    }.bind(this), this.Decode_ToBool = function(e) {
        return this.Decode_ToByte(e) > 0
    }.bind(this), this.Decode_ToEnum = function(e) {
        var t = (this.Decode_ToString(e), this._decode_get_buffer(e, 1)),
            i = this._parser.toByte(t);
        return e.setPosition(e.getPosition() + 1), i
    }.bind(this), this.Decode_ToByte = function(e) {
        var t = this._decode_get_buffer(e, 1),
            i = this._parser.toByte(t);
        return e.setPosition(e.getPosition() + 1), i
    }.bind(this), this.Decode_ToShort = function(e) {
        var t = this._decode_get_buffer(e, 2),
            i = this._parser.toWord(t);
        return e.setPosition(e.getPosition() + 2), i
    }.bind(this), this.Decode_ToUShort = function(e) {
        var t = this._decode_get_buffer(e, 2),
            i = this._parser.toShort(t);
        return e.setPosition(e.getPosition() + 2), i
    }.bind(this), this.Decode_ToInt = function(e) {
        var t = this._decode_get_buffer(e, 4),
            i = this._parser.toInt(t);
        return e.setPosition(e.getPosition() + 4), i
    }.bind(this), this.Decode_ToUInt = function(e) {
        var t = this._decode_get_buffer(e, 4),
            i = this._parser.toDWord(t);
        return e.setPosition(e.getPosition() + 4), i
    }.bind(this), this.Decode_ToInt64 = function(e) {
        for (var t = this._decode_get_buffer(e, 8), i = 0, n = t.length - 1; n >= 0; n--) i = 256 * i + t[n];
        return e.setPosition(e.getPosition() + 8), i
    }.bind(this), this.Decode_ToDouble = function(e) {
        var t = this._decode_get_buffer(e, 8),
            i = this._parser.toDouble(t);
        return e.setPosition(e.getPosition() + 8), i
    }.bind(this), this.Decode_ToString = function(e) {
        var t = this.Decode_ToInt(e),
            i = this._decode_get_buffer(e, t),
            n = String.fromCharCode.apply(null, i),
            o = decodeURIComponent(escape(n));
		
        return e.setPosition(e.getPosition() + t), o
    }.bind(this), this.Decode_ValueType = function(e, t, i) {
        return "number" == typeof e[t] && typeof e[t] >= 0 && typeof e[t] <= 255 ? (e[t] = this.Decode_ToByte(i), !0) : "boolean" == typeof e[t] ? (e[t] = this.Decode_ToBool(i), !0) : "number" == typeof e[t] && typeof e[t] >= 0 && typeof e[t] <= 65535 ? (e[t] = this.Decode_ToUShort(i), !0) : "number" == typeof e[t] && typeof e[t] >= -32768 && typeof e[t] <= 32767 ? (e[t] = this.Decode_ToUShort(i), !0) : "number" == typeof e[t] && typeof e[t] >= 0 && typeof e[t] <= Math.pow(2, 32) - 1 ? (e[t] = this.Decode_ToUInt(i), !0) : "number" == typeof e[t] && typeof e[t] >= Math.pow(2, 31) * -1 && typeof e[t] <= Math.pow(2, 31) - 1 ? (e[t] = this.Decode_ToUInt(i), !0) : "number" == typeof e[t] && typeof e[t] >= Math.pow(2, 52) * -1 && typeof e[t] <= Math.pow(2, 53) - 1 ? (e[t] = this.Decode_ToUInt(i), !0) : "number" == typeof e[t] && typeof e[t] >= 0 && typeof e[t] <= Math.pow(2, 53) - 1 ? (e[t] = this.Decode_ToUInt(i), !0) : "float" == typeof e[t] ? (e[t] = this.Decode_ToDouble(i), !0) : "string" == typeof e[t] ? (e[t] = this.Decode_ToString(i), !0) : void 0
    }.bind(this), this.Decode_ToValueStruct = function(e, t) {
        for (var i in e) e.hasOwnProperty(i) && this.Decode_ValueType(e, i, t)
    }.bind(this), {
        Encode_FromBool: this.Encode_FromBool,
        Encode_FromEnum: this.Encode_FromEnum,
        Encode_FromByte: this.Encode_FromByte,
        Encode_FromShort: this.Encode_FromShort,
        Encode_FromUShort: this.Encode_FromUShort,
        Encode_FromInt: this.Encode_FromInt,
        Encode_FromInt64: this.Encode_FromInt64,
        Encode_FromDouble: this.Encode_FromDouble,
        Encode_FromString: this.Encode_FromString,
        Decode_ToBool: this.Decode_ToBool,
        Decode_ToEnum: this.Decode_ToEnum,
        Decode_ToByte: this.Decode_ToByte,
        Decode_ToShort: this.Decode_ToShort,
        Decode_ToUShort: this.Decode_ToUShort,
        Decode_ToInt: this.Decode_ToInt,
        Decode_ToUInt: this.Decode_ToUInt,
        Decode_ToInt64: this.Decode_ToInt64,
        Decode_ToDouble: this.Decode_ToDouble,
        Decode_ToString: this.Decode_ToString
    }
}()




///////////////////////////////////////
///////////////////////////////////////











_self.gameCommand=null;
_self.gameCode=null;
_self.gameSettings=null;
_self.gameBalanceInCents=null;

///////////////////////////
_self.sceneBullets=[];
_self.sceneFishes=[];
_self.fishesInScene=[];
_self.fishesUpdateInterval=0;

_self.gameData={};
_self.FishCountID=1;

/*---------- fishes paytable ------------*/

var fishPay=[];



fishPay['Fish_1'] = [2];
fishPay['Fish_2'] = [2];
fishPay['Fish_3'] = [3];
fishPay['Fish_4'] = [3];
fishPay['Fish_5'] = [4];
fishPay['Fish_6'] = [4];
fishPay['Fish_7'] = [5];
fishPay['Fish_8'] = [5];
fishPay['Fish_9'] = [6];
fishPay['Fish_10'] = [6];
fishPay['Fish_11'] = [7];
fishPay['Fish_12'] = [7];
fishPay['Fish_13'] = [8];
fishPay['Fish_14'] = [9];
fishPay['Fish_15'] = [10];
fishPay['Fish_16'] = [15];
fishPay['Fish_17'] = [15];
fishPay['Fish_18'] = [20];
fishPay['Fish_19'] = [25];
fishPay['Fish_20'] = [30];
fishPay['Fish_21'] = [30];
fishPay['Fish_22'] = [60];
fishPay['Fish_23'] = [70];
fishPay['Fish_24'] = [80];
fishPay['Fish_25'] = [80];
fishPay['Fish_26'] = [100];
fishPay['Fish_110'] =  [100];




var fishDamage=[];

fishDamage['Fish_1'] = [1,2];
fishDamage['Fish_2'] = [1,2];
fishDamage['Fish_3'] = [1,3];
fishDamage['Fish_4'] = [1,4];
fishDamage['Fish_5'] = [2,5];
fishDamage['Fish_6'] = [2,6];
fishDamage['Fish_7'] = [2,7];
fishDamage['Fish_8'] = [2,8];
fishDamage['Fish_9'] = [2,9];
fishDamage['Fish_10'] = [2,10];
fishDamage['Fish_11'] = [2,12];
fishDamage['Fish_12'] = [2,15];
fishDamage['Fish_13'] = [3,18];
fishDamage['Fish_14'] = [3,20];
fishDamage['Fish_15'] = [3,25];
fishDamage['Fish_16'] = [4,30];
fishDamage['Fish_17'] = [4,40];
fishDamage['Fish_18'] = [5,50];
fishDamage['Fish_19'] = [5,60];

fishDamage['Fish_31'] = [10,80];
fishDamage['Fish_32'] = [10,100];
fishDamage['Fish_33'] = [10,120];
fishDamage['Fish_34'] = [10,150];
fishDamage['Fish_35'] = [10,180];
fishDamage['Fish_36'] = [10,200];


fishDamage['Fish_61'] = [20,500];
fishDamage['Fish_62'] = [20,600];
fishDamage['Fish_63'] = [20,700];

fishDamage['Fish_71'] = [10,200];
fishDamage['Fish_72'] = [10,200];
fishDamage['Fish_73'] = [10,200];
fishDamage['Fish_74'] = [10,200];
fishDamage['Fish_75'] = [10,200];
fishDamage['Fish_76'] = [10,200];

fishDamage['Fish_101'] =  [20,500];
fishDamage['Fish_110'] =  [20,100];


/*----------control fishes on scene------------*/

_self.fishesId=[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,11,11,11,11,11,11,11,11,11,11,11,12,12,12,12,12,12,12,12,12,12,12,12,12,13,13,13,13,13,13,13,13,13,13,13,13,13,13,14,14,14,14,14,14,14,14,14,15,15,15,15,15,15,15,15,16,16,16,16,16,16,17,17,17,17,17,17,17,17,17,17,17,17,17,18,18,18,18,18,18,18,18,19,19,19,19,19,19,19,19,19,19,20,20,20,21,21,21,25,27];



_self.fishesId=utils.ShuffleArray(_self.fishesId);

_self.fishesId_=0;

this.FishesUpdate=function(){
	
var curFishOX=-10;	
var curFishOY=-20;	


var curFishUID=utils.RandomInt(1000,10000);	
var curTime  = new Date();

/*
if(_self.fishesId_==20){
	curFishId=104;
}
*/

if(_self.fishesInScene.length>10 || _self.RoomSettings.setting.group[_self.fishesId_]==undefined){
return;	
}
	
/*------------------------*/
/*------------------------*/	


/*------------------------*/
/*------------------------*/	




//_self.sceneFishes['fish_'+curFishUID]={curFishUID:curFishUID,fishId:curFishId,fishHealth:cFishHealth,fishPay:cFishPay,fishTime:curTime.getTime()+(cRoute._awake*1000)};	

if(_self.fishesId_>_self.RoomSettings.setting.group.length-1 || _self.RoomSettings.setting.group[_self.fishesId_]==undefined){
_self.fishesId_=0;	
}

var airLim=_self.RoomSettings.setting.group[_self.fishesId_].element.length;

_self.fishesId_++;	



var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 3);//command code
ProtocolBuilder.Encode_FromInt(response, _self.fishesId_);//
ProtocolBuilder.Encode_FromInt(response,airLim);//


for(var i=0; i<airLim; i++ ){
	
ProtocolBuilder.Encode_FromInt(response, _self.FishCountID);//	
_self.fishesInScene.push([_self.FishCountID,0]);	
_self.FishCountID++;	

	
}

////console.log('airLim ::: ',airLim)


emitter.emit('outcomingMessage',response.getData(),false);	
	
	
	
//emitter.emit('outcomingMessage',fishPreset,true);		
	

	


	
};

this.RoundTicker=function(isReset=false){
	
_self.RoundTickerCount+=3;	
	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 203);//command code
if( isReset){
ProtocolBuilder.Encode_FromByte(response, 1);//
ProtocolBuilder.Encode_FromByte(response, 0);//
_self.fishesId_=0;	
_self.FishCountID=0;	
 _self.RoundTickerCount=0;
}else{
ProtocolBuilder.Encode_FromByte(response, 0);//	
ProtocolBuilder.Encode_FromByte(response, 0);//
}


ProtocolBuilder.Encode_FromInt(response, _self.RoundTickerCount);
ProtocolBuilder.Encode_FromInt(response, 0);
emitter.emit('outcomingMessage',response.getData(),false);		
	
	
}



this.ResetRound=function(){
	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 203);//command code

ProtocolBuilder.Encode_FromByte(response, 1);//
ProtocolBuilder.Encode_FromByte(response, 0);//
_self.fishesId_=0;	
_self.FishCountID=0;	
 _self.RoundTickerCount=0;
 _self.fishesInScene=[];
ProtocolBuilder.Encode_FromInt(response, _self.RoundTickerCount);
ProtocolBuilder.Encode_FromInt(response, 0);
emitter.emit('outcomingMessage',response.getData(),false);			


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 199);//command code
ProtocolBuilder.Encode_FromInt64(response, 40000000016992+utils.RandomInt(1000,2000));//
emitter.emit('outcomingMessage',response.getData(),false);	

	
}


this.StartFishesUpdate=function(){
_self.StopFishesUpdate();	
_self.fishesUpdateInterval=setInterval(_self.FishesUpdate,10000);	
_self.RoundTickerInterval=setInterval(_self.RoundTicker,3000);	
_self.RoundTickerCount=0;
_self.FishesUpdate();	
};

this.StopFishesUpdate=function(){
	
clearInterval(_self.RoundTickerInterval);
clearInterval(_self.fishesUpdateInterval);
	
};
this.ClearGameData=function(){
	

clearInterval(_self.RoundTickerInterval);
clearTimeout(_self.msgHandlerTicker);
clearInterval(_self.fishesUpdateInterval);
	
};




/*----------control fishes on scene------------*/
                 /*-----------------------*/
                 /*-----------------------*/
                 /*-----------------------*/



 this.Login = async function(step)
{


var balanceInCents,response;

await sys.CreateConnection();	

var balanceInCents=await sys.GetBalance();
_self.gameBalanceInCents=balanceInCents;

var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, 0);
ProtocolBuilder.Encode_FromByte(response, 1);
ProtocolBuilder.Encode_FromByte(response, 1);

_self.UserID=39481;
_self.GameID=10004;

var param='{"Currency":"CNY","DealerID":21923,"Save":{"10002":{},"10003":{},"10004":{"BestRecord":{"Level0Secs":3599,"Level1Secs":3599,"Level2Secs":3599,"Level3Secs":3599},"BuffCount":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0}},"_self.GameID":{},"10006":{},"10101":{},"10102":{}},"UserID":39481,"UserName":"GUESTPASS2803"}';
ProtocolBuilder.Encode_FromString(response, param);
emitter.emit('outcomingMessage',response.getData(),false);	

////////////////

var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, 0);
ProtocolBuilder.Encode_FromByte(response, 2);
ProtocolBuilder.Encode_FromByte(response, 1);
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//gameid
emitter.emit('outcomingMessage',response.getData(),false);	

////////////////
////////////////


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//gameid
ProtocolBuilder.Encode_FromByte(response, 200);//command code
//ProtocolBuilder.Encode_FromInt(response, 200);//

//room 1

ProtocolBuilder.Encode_FromDouble(response, 0.01);//min bet
ProtocolBuilder.Encode_FromDouble(response, 0.10);//max bet
//room 2

ProtocolBuilder.Encode_FromDouble(response, 0.1);//min bet
ProtocolBuilder.Encode_FromDouble(response, 1);//max bet
//room 3

ProtocolBuilder.Encode_FromDouble(response, 1);//min bet
ProtocolBuilder.Encode_FromDouble(response, 10);//max bet
//room 4

ProtocolBuilder.Encode_FromDouble(response, 10);//min bet
ProtocolBuilder.Encode_FromDouble(response, 100);//max bet

emitter.emit('outcomingMessage',response.getData(),false);	


};


 this.Ping = async function(dat)
{

var curTime  = new Date();
	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 203);//command code
ProtocolBuilder.Encode_FromByte(response, 0);//

ProtocolBuilder.Encode_FromInt(response, 0);
ProtocolBuilder.Encode_FromInt(response, 0);

emitter.emit('outcomingMessage',response.getData(),false);	

	

	
	
}




 this.ChangeRate = async function(dat)
{
	
var decodeData=[];
var t = new MemoryStream;
t.initialBuffer(dat.fullRequest);
decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);
decodeData[2]=ProtocolBuilder.Decode_ToByte(t);

if(decodeData[2]==1){

_self.gameData.CurrentBetMeter++;
	
}else{
	
_self.gameData.CurrentBetMeter--;	
	
} 

	
if(_self.gameData.CurrentBetMeter<0){
_self.gameData.CurrentBetMeter=_self.gameData.Bets.length-1;	
}	
if(_self.gameData.CurrentBetMeter>_self.gameData.Bets.length-1){
_self.gameData.CurrentBetMeter=0;	
}	
	

_self.gameData.CurrentBet=_self.gameData.Bets[_self.gameData.CurrentBetMeter];



var CannonState=0;
var cs0=800000;
var cs1= 160000;

 

if(_self.gameData.CurrentBetMeter>=3){

CannonState=1;
	
 cs0=1000000;
cs1= 200000;		
	 
	
}
if(_self.gameData.CurrentBetMeter>=6){

CannonState=2;
	
 cs0=1200000;
cs1= 200000;	
	
}


/////////////////////////////	
	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 7);//command code
ProtocolBuilder.Encode_FromDouble(response, utils.FixNumber(_self.gameData.CurrentBet/100));//bet




emitter.emit('outcomingMessage',response.getData(),false);		


	
}



/*-----------destroy-------------*/
 this.DestroyEnemy = async function(dat)
{

/*----------------------*/
/*----------------------*/

var decodeData=[];
var t = new MemoryStream;
t.initialBuffer(dat.fullRequest);



decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);
decodeData[2]=ProtocolBuilder.Decode_ToByte(t);
decodeData[3]=ProtocolBuilder.Decode_ToInt(t);


////console.log('_self.fishesInScene decodeData::: '+decodeData);

var clearArr=false;
var clearArrCnt=0;

for(var i=0; i<_self.fishesInScene.length; i++){
	
	
if(_self.fishesInScene[i][0] == decodeData[3]){	
	
_self.fishesInScene[i][1]--;	
_self.fishesInScene[i][2]=decodeData[2];	
	

}


if(_self.fishesInScene[i][1]<=-2){	
clearArr=true;
}	
if(_self.fishesInScene[i][1]<=-1){	
clearArrCnt++;
}

}

////console.log('TTTT ::: ',clearArrCnt,_self.fishesInScene.length , clearArr);

if(clearArrCnt>=_self.fishesInScene.length){
	
_self.fishesInScene=[];

}

////console.log('_self.fishesInScene ::: ',_self.fishesInScene);



}

/*-----------simple hit--------------*/
 this.Hit = async function(dat)
{

/*----------------------*/
/*----------------------*/

var decodeData=[];
var t = new MemoryStream;
t.initialBuffer(dat.fullRequest);



decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);

 //ProtocolBuilder.Encode_FromByte(S, l.type), ProtocolBuilder.Encode_FromInt(S, l.id), ProtocolBuilder.Encode_FromDouble(S, p[y]._ante)

var seat_id=0;
var bullet_id=0;
var _type=ProtocolBuilder.Decode_ToByte(t);
var t_length=0;
var hits=[];			
hits.push(ProtocolBuilder.Decode_ToInt(t));
var bet=ProtocolBuilder.Decode_ToDouble(t);


var curTime  = new Date();	
/*----------------------*/

if(_self.sceneFishes['fish_'+hits[0] ]==undefined){
var cFishHealth=utils.RandomInt(1,15);	

if(fishPay['Fish_'+_type] ==undefined){
var cFishPay=10;	
}else{
var cFishPay=fishPay['Fish_'+_type] ;	
}


var lTime=40;

if(_type==110){
	lTime=600;
}

_self.sceneFishes['fish_'+hits[0] ]={curFishUID:hits[0] ,fishId:_type,fishHealth:cFishHealth,fishPay:cFishPay,fishTime:curTime.getTime()+(lTime*1000)};		
}



for(var cf in _self.sceneFishes){

if(curTime.getTime()>_self.sceneFishes[cf].fishTime){
	
delete _self.sceneFishes[cf];	
	
}	



}	

/*----------------------*/


	
var fullBombId=25;
var localBombId=27;


var totalWin=0;	
	
_self.bet=bet;	
	
/*--------------*/
if(sys.conn.connection._closing){
await sys.CreateConnection();	
}
/*-------------*/
await sys.StartTransaction();	
var startBalance=await sys.GetBalanceB();	

bet=parseFloat(bet);
if(startBalance<bet || bet<0.0001 || !Number.isFinite(bet) ){
emitter.emit('Error','Invalid balance or bet');	
sys.Rollback();   	
return;	
}	







await sys.UpdateJackpots(bet);

if(sys.address>0 && sys.count_balance<=0){
sys.shopPercent=100;	
}else if(sys.count_balance<=0){
sys.shopPercent=100;	
}

if(sys.shopPercent>0){
var sumToBank=(bet/100)*sys.shopPercent;		
}else{
var sumToBank=bet;		
}
	
await sys.SetBalance(-bet);	
await sys.SetBank(sumToBank,'bet');	



/*-----------------------------*/	
var winsArr=[];
var winsArr2=[];
var freeInfo='';
/*-----------------------------*/	

var targetFishes=hits;
var gameBank=await sys.GetBank();	

/*full bomb*/

var fishDmgValue=1;

/*------------------------------*/
var isBomb=false;
var isBombId=0;
var isBombType='';
var bombArr=[61,62,71,72,73,74,75,76];
/*------------------------------*/

for(var fi=0; fi<targetFishes.length; fi++){

var cfish=targetFishes[fi];



if(_self.sceneFishes['fish_'+cfish]!=undefined){
	


	
	

_self.sceneFishes['fish_'+cfish].fishHealth-=fishDmgValue;

var tmpWin=_self.sceneFishes['fish_'+cfish].fishPay*bet;
var tmpWin0=0;


////console.log('FISH ID :: '+_self.sceneFishes['fish_'+cfish].fishId);

/*-----------------------------*/	

//limit control




if(_self.sceneFishes['fish_'+cfish].fishHealth<=0 && bombArr.indexOf(_self.sceneFishes['fish_'+cfish].fishId) !=-1){

isBomb =true;	
isBombId=_self.sceneFishes['fish_'+cfish].fishId;	
	
	
}

//console.log('HIT ::: ',_self.sceneFishes['fish_'+hits[0]].fishHealth,_type,(tmpWin+totalWin+tmpWin0),gameBank);

if(_self.sceneFishes['fish_'+cfish].fishHealth<=0 && (tmpWin+totalWin+tmpWin0)<=gameBank){


totalWin+=tmpWin;
winsArr.push({fish_id:cfish,kill:1,exp_win:tmpWin0, win:(utils.FixNumber(tmpWin)),hp:0});		
	
delete _self.sceneFishes['fish_'+cfish];	
	
for(var i=0; i<_self.fishesInScene.length; i++){
	
	
if(_self.fishesInScene[i][0] == cfish){	
	
_self.fishesInScene.splice(i,1);

}


}	
	
	
}else{
winsArr.push({fish_id:cfish,kill:0, win:0,exp_win:0,hp:100});			
}


	
}	
	
}




	
var endBalance=startBalance-bet+totalWin;
_self.gameBalanceInCents=utils.FixNumber(endBalance);

var response=[];

if(totalWin>0){
	
await sys.SetBalance(totalWin);	
await sys.SetBank(-totalWin,'');	

if(_type==110){
setTimeout(_self.ResetRound,10000);
}


}


	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 2);//command code

ProtocolBuilder.Encode_FromInt(response, 1);//





for(var kf=0; kf<winsArr.length; kf++){

            ProtocolBuilder.Encode_FromByte(response,_type); 
			ProtocolBuilder.Encode_FromInt(response,winsArr[kf].fish_id);
			ProtocolBuilder.Encode_FromByte(response,1);
			ProtocolBuilder.Encode_FromByte(response,winsArr[kf].kill);
			ProtocolBuilder.Encode_FromDouble(response,utils.FixNumber(bet)); 
			ProtocolBuilder.Encode_FromDouble(response,winsArr[kf].win);
			ProtocolBuilder.Encode_FromDouble(response,_self.gameBalanceInCents);
			ProtocolBuilder.Encode_FromBool(response,false);
			ProtocolBuilder.Encode_FromInt(response,winsArr[kf].hp);

	
}
ProtocolBuilder.Encode_FromInt(response,1);

emitter.emit('outcomingMessage',response.getData(),false);	


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 255);//command code
ProtocolBuilder.Encode_FromDouble(response, _self.gameBalanceInCents);//
emitter.emit('outcomingMessage',response.getData(),false);	







/////////////////////////////////////////








 await sys.Commit();	
 sys.SaveLogReport({balance:endBalance,bet:bet,win:totalWin});	




	



	
};





/*-----------simple hit--------------*/
 this.HitBomb = async function(dat)
{

/*----------------------*/
/*----------------------*/

var decodeData=[];
var t = new MemoryStream;
t.initialBuffer(dat.fullRequest);



decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);

 //ProtocolBuilder.Encode_FromByte(S, l.type), ProtocolBuilder.Encode_FromInt(S, l.id), ProtocolBuilder.Encode_FromDouble(S, p[y]._ante)

var seat_id=0;
var bullet_id=0;
var nkn=ProtocolBuilder.Decode_ToInt(t);
var t_length=ProtocolBuilder.Decode_ToInt(t);

var hits=[];	

for(var fidc=0; fidc<t_length; fidc++ ){
		
hits.push(ProtocolBuilder.Decode_ToInt(t));
var _type=ProtocolBuilder.Decode_ToByte(t);
var bet=ProtocolBuilder.Decode_ToDouble(t);

var curTime  = new Date();	
/*----------------------*/

if(_self.sceneFishes['fish_'+hits[0] ]==undefined){
var cFishHealth=utils.RandomInt(1,10);	
var cFishPay=10;
_self.sceneFishes['fish_'+hits[0] ]={curFishUID:hits[0] ,fishId:_type,fishHealth:cFishHealth,fishPay:cFishPay,fishTime:curTime.getTime()+(30*1000)};		
}


}








for(var cf in _self.sceneFishes){

if(curTime.getTime()-_self.sceneFishes[cf].fishTime>=30000){
	
delete _self.sceneFishes[cf];	
	
}	



}	

/*----------------------*/
////console.log('HIT ::: ',seat_id,bullet_id,_type,t_length,hits,bet);

	
var fullBombId=25;
var localBombId=27;


var totalWin=0;	
	
_self.bet=bet;	
	
/*--------------*/
if(sys.conn.connection._closing){
await sys.CreateConnection();	
}
/*-------------*/
await sys.StartTransaction();	
var startBalance=await sys.GetBalanceB();	

bet=parseFloat(bet);
if(startBalance<bet || bet<0.0001 || !Number.isFinite(bet) ){
emitter.emit('Error','Invalid balance or bet');	
sys.Rollback();   	
return;	
}	







await sys.UpdateJackpots(bet);

if(sys.address>0 && sys.count_balance<=0){
sys.shopPercent=100;	
}else if(sys.count_balance<=0){
sys.shopPercent=100;	
}

if(sys.shopPercent>0){
var sumToBank=(bet/100)*sys.shopPercent;		
}else{
var sumToBank=bet;		
}
	
await sys.SetBalance(-bet);	
await sys.SetBank(sumToBank,'bet');	



/*-----------------------------*/	
var winsArr=[];
var winsArr2=[];
var freeInfo='';
/*-----------------------------*/	

var targetFishes=hits;
var gameBank=await sys.GetBank();	

/*full bomb*/

var fishDmgValue=1;

/*------------------------------*/
var isBomb=false;
var isBombId=0;
var isBombType='';
var bombArr=[61,62,71,72,73,74,75,76];
/*------------------------------*/

for(var fi=0; fi<targetFishes.length; fi++){

var cfish=targetFishes[fi];



if(_self.sceneFishes['fish_'+cfish]!=undefined){
	


	
	

_self.sceneFishes['fish_'+cfish].fishHealth-=fishDmgValue;

var tmpWin=_self.sceneFishes['fish_'+cfish].fishPay*bet;
var tmpWin0=0;


////console.log('FISH ID :: '+_self.sceneFishes['fish_'+cfish].fishId);

/*-----------------------------*/	

//limit control




if(_self.sceneFishes['fish_'+cfish].fishHealth<=0 && bombArr.indexOf(_self.sceneFishes['fish_'+cfish].fishId) !=-1){

isBomb =true;	
isBombId=_self.sceneFishes['fish_'+cfish].fishId;	
	
	
}


if(_self.sceneFishes['fish_'+cfish].fishHealth<=0 && (tmpWin+totalWin+tmpWin0)<=gameBank){


totalWin+=tmpWin;
winsArr.push({fish_id:cfish,kill:1,exp_win:tmpWin0, win:(utils.FixNumber(tmpWin)),hp:0});		
	
delete _self.sceneFishes['fish_'+cfish];	
	
for(var i=0; i<_self.fishesInScene.length; i++){
	
	
if(_self.fishesInScene[i][0] == cfish){	
	
_self.fishesInScene.splice(i,1);

}


}	
	
	
}else{
winsArr.push({fish_id:cfish,kill:0, win:0,exp_win:0,hp:100});			
}


	
}	
	
}




	
var endBalance=startBalance-bet+totalWin;
_self.gameBalanceInCents=utils.FixNumber(endBalance);

var response=[];

if(totalWin>0){
	
await sys.SetBalance(totalWin);	
await sys.SetBank(-totalWin,'');	





}


	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 2);//command code

ProtocolBuilder.Encode_FromInt(response, 1);//





for(var kf=0; kf<winsArr.length; kf++){

            ProtocolBuilder.Encode_FromByte(response,_type); 
			ProtocolBuilder.Encode_FromInt(response,winsArr[kf].fish_id);
			ProtocolBuilder.Encode_FromByte(response,1);
			ProtocolBuilder.Encode_FromByte(response,winsArr[kf].kill);
			ProtocolBuilder.Encode_FromDouble(response,utils.FixNumber(bet)); 
			ProtocolBuilder.Encode_FromDouble(response,winsArr[kf].win);
			ProtocolBuilder.Encode_FromDouble(response,_self.gameBalanceInCents);
			ProtocolBuilder.Encode_FromBool(response,false);
			ProtocolBuilder.Encode_FromInt(response,winsArr[kf].hp);

	
}
ProtocolBuilder.Encode_FromInt(response,1);

emitter.emit('outcomingMessage',response.getData(),false);	


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 255);//command code
ProtocolBuilder.Encode_FromDouble(response, _self.gameBalanceInCents);//
emitter.emit('outcomingMessage',response.getData(),false);	







/////////////////////////////////////////








 await sys.Commit();	
 sys.SaveLogReport({balance:endBalance,bet:bet,win:totalWin});	




	



	
};





 this.ExitRoom = async function(dat)
{


////////////

_self.StopFishesUpdate();	

var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 10);//command code
emitter.emit('outcomingMessage',response.getData(),false);	
	
	
}


 this.EnterRoom = async function(dat)
{
	
	
/*-------gameSettings-------*/		
_self.fishesInScene=[];	
_self.sceneFishes=[];
_self.fishesId_=0;	
_self.FishCountID=0;	
 _self.RoundTickerCount=0;
 
var gameSettings=await sys.GetSettings();
_self.gameSettings={};
_self.gameSettings.bets=gameSettings.bet.split(',');



_self.gameSettings.limits=[];
_self.gameSettings.limits['time1']=gameSettings.time1*60;
_self.gameSettings.limits['time2']=gameSettings.time2*60;
_self.gameSettings.limits['time3']=gameSettings.time3*60;
_self.gameSettings.limits['sum_win1']=gameSettings.sum_win1;
_self.gameSettings.limits['sum_win2']=gameSettings.sum_win2;
_self.gameSettings.limits['sum_win3']=gameSettings.sum_win3;
_self.gameSettings.limits['one_win1']=gameSettings.one_win1;
_self.gameSettings.limits['one_win2']=gameSettings.one_win2;
_self.gameSettings.limits['one_win3']=gameSettings.one_win3;

///////////////////////////////
sys.bankType=gameSettings.gamebank;	
	
/*-------init game data-------*/	

_self.gameData.slotState='';	
_self.gameData.freeInfo={count:-1,index:0};	
	
/*----------------*/

	
var balanceInCents=await sys.GetBalance();
_self.gameBalanceInCents=balanceInCents;


/*--------------ENTER ROOM---------------------*/	

var curTime  = new Date();
	
var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 253);//command code
ProtocolBuilder.Encode_FromInt64(response, sys.userName);//

emitter.emit('outcomingMessage',response.getData(),false);	


	
/////////////////////////////

var decodeData=[];
var t = new MemoryStream;
t.initialBuffer(dat.fullRequest);
decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);
decodeData[2]=ProtocolBuilder.Decode_ToByte(t);

_self.gameData.CurrentBetMeter=0;	

if(decodeData[2]==0){
_self.gameData.CurrentBet=1;	
_self.gameData.Bets=[1,2,3,4,5,6,7,8,9,10];	
}
if(decodeData[2]==1){
_self.gameData.CurrentBet=10;	
_self.gameData.Bets=[10,20,30,40,50,60,70,80,90,100];	
}
if(decodeData[2]==2){
_self.gameData.CurrentBet=100;	
_self.gameData.Bets=[100,200,300,400,500,600,700,800,900,1000];	
}
if(decodeData[2]==3){
_self.gameData.CurrentBet=1000;	
_self.gameData.Bets=[1000,2000,3000,4000,5000,6000,7000,8000,9000,10000];	
}



/////////////////////////////
//ProtocolBuilder.Encode_FromInt(response, 39481);//uid


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 1);//command code

ProtocolBuilder.Encode_FromByte(response, decodeData[2]);//

ProtocolBuilder.Encode_FromString(response, 'settings_level'+decodeData[2]+'_01.xml');//command code
ProtocolBuilder.Encode_FromInt(response, 9);


var fs = require('fs');
var jst=fs.readFileSync('./arcade_server/games/ThunderFighterCQ9/settings_level'+decodeData[2]+'_01.json', 'utf8');

_self.RoomSettings=JSON.parse(jst);



var bufArr=[0,9,10,8,3,10,7,2,10,10];

for(var j=1;j<=9;j++){

ProtocolBuilder.Encode_FromInt(response, j);
ProtocolBuilder.Encode_FromInt(response, bufArr[j]);
	
}

emitter.emit('outcomingMessage',response.getData(),false);	



////////////
////////////
////////////


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, 0);//system command
ProtocolBuilder.Encode_FromByte(response, 255);//command code
ProtocolBuilder.Encode_FromByte(response, 1);//command code
ProtocolBuilder.Encode_FromDouble(response, balanceInCents);//
emitter.emit('outcomingMessage',response.getData(),false);	

var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, 0);//system command
ProtocolBuilder.Encode_FromByte(response, 255);//command code
ProtocolBuilder.Encode_FromByte(response, 1);//command code
ProtocolBuilder.Encode_FromDouble(response, balanceInCents);//
emitter.emit('outcomingMessage',response.getData(),false);	

var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 199);//command code
ProtocolBuilder.Encode_FromInt64(response, 40000000086992);//
emitter.emit('outcomingMessage',response.getData(),false);	
/////////////////////////////	
	


var response = new MemoryStream;
ProtocolBuilder.Encode_FromInt(response, _self.GameID);//system command
ProtocolBuilder.Encode_FromByte(response, 255);//command code
ProtocolBuilder.Encode_FromDouble(response, balanceInCents);//
emitter.emit('outcomingMessage',response.getData(),false);	

_self.StartFishesUpdate();

	
}

 this.IncomingDataHandler = async function(data)
{

var decodeData=[];

if(data.fullRequest!=undefined){
var t = new MemoryStream;
t.initialBuffer(data.fullRequest);
decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);
}

if(decodeData[1]==8){
 _self.DestroyEnemy(data); 
}else{

_self.msgHandlerStack.push(data);
	
}



};


 this.MessageCheck = async function(data)
{



if(_self.msgHandler==1 && _self.msgHandlerStack.length>0){
	
//////console.log('_self.msgHandler=0');	
_self.msgHandler=0;		
	
var dt=_self.msgHandlerStack.shift();
	
try{	
await _self.MessageHandler(dt);	
}catch(e){
	
var detailError={
	
msg:e.message,	
stack:e.stack,	
desc:'Game error. Check code.',	
	
};	

sys.Rollback();
sys.InternalErrorLog(JSON.stringify(detailError));	
_self.msgHandler=1;	
}	

	

//////console.log('_self.msgHandler=1');				
}



};

 this.MessageHandler = async function(data)
{

////console.log('DATA ',data);

_self.gameCommand='';

try{

var decodeData=[];

var t = new MemoryStream;
t.initialBuffer(data.fullRequest);


decodeData[0]=ProtocolBuilder.Decode_ToInt(t);
decodeData[1]=ProtocolBuilder.Decode_ToByte(t);

if(decodeData[0]==0){
_self.gameCommand='login';	
}else if(decodeData[1]==1){
_self.gameCommand='enterroom';	
}else if(decodeData[1]==2){
_self.gameCommand='hit';	
}else if(decodeData[1]==4){
_self.gameCommand='hitbomb';	
}else if(decodeData[1]==7){
_self.gameCommand='changerate';	
}else if(decodeData[1]==10){
_self.gameCommand='exitroom';	
}


}catch(e){
	
	
}

//console.log('DATA ',decodeData);





switch(_self.gameCommand){
	
case 'ping':

 _self.Ping(data); 

break;
	
	
case 'login':

 _self.Login(data); 

break;	
case 'exitroom':

 _self.ExitRoom(data); 

break;
case 'enterroom':

 _self.EnterRoom(data); 

break;
case 'changbackstage':

 _self.ChangeBackstage(data.gameData); 

break;

case 'fire':

 _self.Fire(data.gameData); 

break;

case 'hit':

 await  _self.Hit(data); 

break;
case 'hitbomb':

 await  _self.HitBomb(data); 

break;

case 'changerate':

 _self.ChangeRate(data); 

break;
case 'canclelocking':

 _self.CancleLocking(data.gameData); 

break;
case 'changelocking':

 _self.ChangeLocking(data.gameData); 

break;
case 'leaveroom':

break;
case 'electrichit':

break;

default:

////////console.log('Unknow command :::::: ' ,_self.gameCommand);

break;


}


_self.msgHandler=1;
};


_self.msgHandler=1;
_self.msgHandlerStack=[];
_self.msgHandlerTicker=0;

_self.msgHandlerTicker=setInterval(_self.MessageCheck,20);

return _self;	
	
}



module.exports = { Game }
