var config = {
    map: {
        '*': {
            deleteCard:            'Veriteworks_Gmo/deleteCard'
        }
    }
};

window.submitToGmo = function submitToGmo (response) {
    var tokenError = {
        "000": "トークン取得正常終了 [Token acquisition completed normally].",
        "100": "カード番号必須チェックエラー [Card number required check error].",
        "101": "カード番号フォーマットエラー(数字以外を含む) [Card number format error (including non-numbers)].",
        "102": "カード番号フォーマットエラー(10-16 桁の範囲外) [Card number format error (outside the range of 10-16 digits)].",
        "110": "有効期限必須チェックエラー [Expiration date required check error].",
        "111": "有効期限フォーマットエラー(数字以外を含む) [Expiration date format error (including non-numeric characters)].",
        "112": "有効期限フォーマットエラー(6 又は 4 桁以外) [Expiration date format error (other than 6 or 4 digits)].",
        "113": "有効期限フォーマットエラー(月が 13 以上) [Expiration date format error (month is 13 or more)].",
        "121": "セキュリティコードフォーマットエラー(数字以外を含む) [Security code format error (including non-numeric characters)].",
        "122": "セキュリティコード桁数エラー [Security code digit error].",
        "131": "名義人フォーマットエラー(半角英数字、一部の記号以外を含む) [Holder format error (including half-width alphanumeric characters and some symbols)].",
        "132": "名義人フォーマットエラー(51 桁以上) [Holder format error (51 digits or more)].",
        "141": "発行数フォーマットエラー(数字以外を含む) [Number of issues Format error (including non-numbers)].",
        "142": "発行数フォーマットエラー(1-10 の範囲外) [Number of issues format error (outside the range of 1-10)].",
        "150": "カード情報を暗号化した情報必須チェックエラー [Information required to encrypt card information Check error].",
        "160": "ショップ ID 必須チェックエラー [Shop ID required check error].",
        "161": "ショップ ID フォーマットエラー(14 桁以上) [Shop ID format error (14 digits or more)].",
        "162": "ショップ ID フォーマットエラー(半角英数字以外) [Shop ID format error (other than half-width alphanumeric characters)].",
        "170": "公開鍵ハッシュ値必須チェックエラー [Public key hash value required check error].",
        "180": "ショップ ID または公開鍵ハッシュ値がマスターに存在しない [Shop ID or public key hash value does not exist on master].",
        "190": "カード情報(Encrypted)が復号できない [Card information (Encrypted) cannot be decrypted].",
        "191": "カード情報(Encrypted)復号化後フォーマットエラー [Card information (Encrypted) Format error after decryption].",
        "501": "トークン用パラメータ(id)が送信されていない [Token parameter (id) has not been sent].",
        "502": "トークン用パラメータ(id)がマスターに存在しない [Parameter (id) for token does not exist in master].",
        "511": "トークン用パラメータ(cardInfo)が送信されていない [Token parameter (cardInfo) has not been sent].",
        "512": "トークン用パラメータ(cardInfo)が復号できない [Token parameter (cardInfo) cannot be decrypted].",
        "521": "トークン用パラメータ(key)が送信されていない [Token parameter (key) has not been sent].",
        "522": "トークン用パラメータ(key)が復号できない [Token parameter (key) cannot be decrypted].",
        "531": "トークン用パラメータ(callBack)が送信されていない [Token parameter (callBack) has not been sent].",
        "541": "トークン用パラメータ(hash)が存在しない [Parameter for token (hash) does not exist].",
        "551": "トークン用 apikey が存在しない ID [ID for which apikey for token does not exist].",
        "552": "トークン用 apikey が有効ではない [Apikey for token is not valid].",
        "901": "マルチペイメント内部のシステムエラー [System error inside multipayment].",
        "902": "処理が混み合っている [Processing is crowded]."
    };
    var resultCodeDesc = tokenError[response.resultCode];

    if( response.resultCode != '000' ){
        jQuery('#veritegmo_cc_cc_error').val(resultCodeDesc);
    }else {
        jQuery('#veritegmo_cc_cc_number').val('');
        jQuery('#veritegmo_cc_expiration').prop('selectedIndex', 0);
        jQuery('#veritegmo_cc_expiration_yr').prop('selectedIndex', 0);
        jQuery('#veritegmo_cc_cc_cid').val('');
        jQuery('#veritegmo_cc_cc_holder').val('');
        jQuery('#veritegmo_cc_cc_token').val(response.tokenObject.token);
    }
};

window.submitToGmoMulti = function submitToGmoMulti (response) {
    var tokenError = {
        "000": "トークン取得正常終了 [Token acquisition completed normally].",
        "100": "カード番号必須チェックエラー [Card number required check error].",
        "101": "カード番号フォーマットエラー(数字以外を含む) [Card number format error (including non-numbers)].",
        "102": "カード番号フォーマットエラー(10-16 桁の範囲外) [Card number format error (outside the range of 10-16 digits)].",
        "110": "有効期限必須チェックエラー [Expiration date required check error].",
        "111": "有効期限フォーマットエラー(数字以外を含む) [Expiration date format error (including non-numeric characters)].",
        "112": "有効期限フォーマットエラー(6 又は 4 桁以外) [Expiration date format error (other than 6 or 4 digits)].",
        "113": "有効期限フォーマットエラー(月が 13 以上) [Expiration date format error (month is 13 or more)].",
        "121": "セキュリティコードフォーマットエラー(数字以外を含む) [Security code format error (including non-numeric characters)].",
        "122": "セキュリティコード桁数エラー [Security code digit error].",
        "131": "名義人フォーマットエラー(半角英数字、一部の記号以外を含む) [Holder format error (including half-width alphanumeric characters and some symbols)].",
        "132": "名義人フォーマットエラー(51 桁以上) [Holder format error (51 digits or more)].",
        "141": "発行数フォーマットエラー(数字以外を含む) [Number of issues Format error (including non-numbers)].",
        "142": "発行数フォーマットエラー(1-10 の範囲外) [Number of issues format error (outside the range of 1-10)].",
        "150": "カード情報を暗号化した情報必須チェックエラー [Information required to encrypt card information Check error].",
        "160": "ショップ ID 必須チェックエラー [Shop ID required check error].",
        "161": "ショップ ID フォーマットエラー(14 桁以上) [Shop ID format error (14 digits or more)].",
        "162": "ショップ ID フォーマットエラー(半角英数字以外) [Shop ID format error (other than half-width alphanumeric characters)].",
        "170": "公開鍵ハッシュ値必須チェックエラー [Public key hash value required check error].",
        "180": "ショップ ID または公開鍵ハッシュ値がマスターに存在しない [Shop ID or public key hash value does not exist on master].",
        "190": "カード情報(Encrypted)が復号できない [Card information (Encrypted) cannot be decrypted].",
        "191": "カード情報(Encrypted)復号化後フォーマットエラー [Card information (Encrypted) Format error after decryption].",
        "501": "トークン用パラメータ(id)が送信されていない [Token parameter (id) has not been sent].",
        "502": "トークン用パラメータ(id)がマスターに存在しない [Parameter (id) for token does not exist in master].",
        "511": "トークン用パラメータ(cardInfo)が送信されていない [Token parameter (cardInfo) has not been sent].",
        "512": "トークン用パラメータ(cardInfo)が復号できない [Token parameter (cardInfo) cannot be decrypted].",
        "521": "トークン用パラメータ(key)が送信されていない [Token parameter (key) has not been sent].",
        "522": "トークン用パラメータ(key)が復号できない [Token parameter (key) cannot be decrypted].",
        "531": "トークン用パラメータ(callBack)が送信されていない [Token parameter (callBack) has not been sent].",
        "541": "トークン用パラメータ(hash)が存在しない [Parameter for token (hash) does not exist].",
        "551": "トークン用 apikey が存在しない ID [ID for which apikey for token does not exist].",
        "552": "トークン用 apikey が有効ではない [Apikey for token is not valid].",
        "901": "マルチペイメント内部のシステムエラー [System error inside multipayment].",
        "902": "処理が混み合っている [Processing is crowded]."
    };
    var resultCodeDesc = tokenError[response.resultCode];
    if( response.resultCode != '000' ){
        jQuery('#veritegmo_ccmulti_cc_error').val(resultCodeDesc);
    }else {
        jQuery('#veritegmo_ccmulti_cc_number').val('');
        jQuery('#veritegmo_ccmulti_expiration').prop('selectedIndex', 0);
        jQuery('#veritegmo_ccmulti_expiration_yr').prop('selectedIndex', 0);
        jQuery('#veritegmo_ccmulti_cc_cid').val('');
        jQuery('#veritegmo_ccmulti_cc_holder').val('');
        jQuery('#veritegmo_ccmulti_cc_token').val(response.tokenObject.token);
    }
};
