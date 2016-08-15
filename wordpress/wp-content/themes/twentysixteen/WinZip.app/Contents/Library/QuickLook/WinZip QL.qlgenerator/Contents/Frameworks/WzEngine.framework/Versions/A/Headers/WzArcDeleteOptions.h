//
//  WzArcDeleteOptions.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzDynamicContainer.h"

@protocol WzArchive;

@interface WzArcDeleteOptions : WzDynamicContainer {
	NSMutableArray *includeFiles;
	NSMutableArray *excludeFiles;
}

// operations
- (void)includeFile:(NSString *)path recurse:(BOOL)recurse;
- (void)excludeFile:(NSString *)path recurse:(BOOL)recurse;

@end