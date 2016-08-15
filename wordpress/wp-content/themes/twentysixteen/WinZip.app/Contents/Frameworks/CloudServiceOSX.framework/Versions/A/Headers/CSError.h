//
//  CSError.h
//  CloudServiceSDK
//
//  Created by User on 7/23/13.
//  Copyright (c) 2013 User. All rights reserved.
//

extern NSString* CSErrorDomain;

// Error codes in the com.glority.cloudservice domain represent the HTTP status code if less than 1000
typedef enum {
    CSErrorNone = 0,
    CSErrorGenericError = 1000,
    CSErrorFileNotFound,
    CSErrorInsufficientDiskSpace,
    CSErrorIllegalFileType, // Error sent if you try to upload a directory
    CSErrorInvalidResponse, // Sent when the client does not get valid JSON when it's expecting it
    CSErrorNotSupported,
    CSErrorInvalidFileName,
    CSErrorInvalidArgument,
    CSErrorLoginFailed,
    CSErrorCancelByUser,
    CSErrorNetworkConnectionLost,
    CSErrorNetworkConnectionTimeout,
    CSErrorRateLimitAccesses,
    CSErrorInvalidAccessToken
} CSErrorCode;
