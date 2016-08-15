//
//  CloudServiceFactory.h
//  CloudServiceSDK
//
//  Created by User on 7/25/13.
//  Copyright (c) 2013 User. All rights reserved.
//

#import "CloudService.h"

@interface CloudServiceFactory : NSObject

//require called in Appdelegate
+ (void)setAppName:(NSString *)appName;
+ (void)enableDropboxWithClientId:(NSString *)clientId clientSecret:(NSString *)clientSecret;
+ (void)enableGoogleDriveWithClientId:(NSString *)clientId clientSecret:(NSString *)clientSecret;
+ (void)enableOneDriveWithClientId:(NSString *)clientId clientSecret:(NSString *)clientSecret;

+ (id<CloudService>)sharedDropboxService;
+ (id<CloudService>)sharedGoogleDriveService;
+ (id<CloudService>)sharedOneDriveService;

#if !TARGET_OS_IPHONE
+ (id<CloudService>)sharedZipShareService;
#else
+ (id<CloudService>)sharedGettService;
#endif

@end
