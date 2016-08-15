//
//  CloudAccountInfo.h
//  CloudServiceSDK
//
//  Created by User on 7/23/13.
//  Copyright (c) 2013 User. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface CloudAccountInfo : NSObject

@property (nonatomic, readonly) NSString *userId;
@property (nonatomic, readonly) NSString *userName;
@property (nonatomic, readonly) NSString *email;

- (id)initWithUserId:(NSString *)userId userName:(NSString *)userName email:(NSString *)email;

@end
