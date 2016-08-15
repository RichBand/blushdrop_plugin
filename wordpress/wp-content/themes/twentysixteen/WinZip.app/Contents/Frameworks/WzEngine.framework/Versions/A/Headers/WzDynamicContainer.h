//
//  WzDynamicContainer.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>

typedef struct _WzDynTable WzDynTable;

@interface WzDynamicContainer : NSObject {
	NSDictionary   *options;
}

+ (const WzDynTable *)dynamicTable;
- (void)applyOptionsToObject:(void *)object;

@end
