//
//  CloudQuota.h
//  CloudServiceSDK
//
//  Created by User on 7/23/13.
//  Copyright (c) 2013 User. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface CloudQuota : NSObject

@property (nonatomic) NSNumber *available;
@property (nonatomic) NSNumber *total;
@property (nonatomic) NSNumber *maxUploadSize;

- (id)initWithAvailable:(NSNumber *)available total:(NSNumber *)total maxUploadSize:(NSNumber *)maxUploadSize;

@end
