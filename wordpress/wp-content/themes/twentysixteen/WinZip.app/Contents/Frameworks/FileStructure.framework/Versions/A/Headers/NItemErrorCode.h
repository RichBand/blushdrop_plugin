//
//  NItemErrorCode.h
//  WinZip
//
//  Created by lv.haohang on 14-3-10.
//  Copyright (c) 2014年 Winzip. All rights reserved.
//

#ifndef WinZip_NItemErrorCode_h
#define WinZip_NItemErrorCode_h

typedef enum {
    Success = 0,
    GenericError = 1001,
    RenameItemNameAlreadyToken = 2001,
    RenameItemNameInvalid = 2002,
    NewFolderNameAlreadyTaken = 3001
} NResultCode;

static NResultCode ErrorCodeToNResultCodeCode(long code) {
    switch (code) {
        case 403:
            return NewFolderNameAlreadyTaken;
        default:
            return GenericError;
    }
}

#endif
