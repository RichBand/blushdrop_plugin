//
//  WzArchiveFactory.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzArchive.h"

@interface WzArchiveFactory : NSObject {

}

+ (NSString *)detectArchiveTypeAtPath:(NSString *)name;
+ (id<WzArchive>)archiveWithName:(NSString *)name andType:(NSString *)archiveType;

@end
