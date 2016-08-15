//
//  WzArcRenameOptions.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzDynamicContainer.h"

@protocol WzArchive;

@interface WzArcRenameOptions : WzDynamicContainer {
	NSMutableArray *renameFiles;
}

// operations
- (void)addRenameFile:(NSString *)path to:(NSString *)newPath stripPath:(BOOL)stripPath;

@end