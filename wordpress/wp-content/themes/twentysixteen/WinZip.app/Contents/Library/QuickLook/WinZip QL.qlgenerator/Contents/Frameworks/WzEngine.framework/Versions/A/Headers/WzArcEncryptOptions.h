//
//  WzArcEncryptOptions.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzDynamicContainer.h"

@protocol WzArchive;

@interface WzArcEncryptOptions : WzDynamicContainer {
	
}

@property (assign) NSString *password;
@property (assign) NSString *tempPath;
@property (assign) WzEncryptionMethodOption encryptionMethod;

@end